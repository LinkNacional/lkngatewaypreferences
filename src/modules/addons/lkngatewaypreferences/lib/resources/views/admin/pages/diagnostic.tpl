{extends "../layout.tpl"}

{$page="diagnostic"}
{$title="Module Diagnostic"}

{block "content"}
    <div style="max-width: 1000px;">
        <h3>lkngatewaypreferences Module Diagnostic</h3>
        <p>This page shows detailed information about module status, configuration, and fraud orders.</p>

        <div id="diagnostic-loading" style="text-align: center; padding: 40px;">
            <p>Loading diagnostic information...</p>
        </div>

        <div id="diagnostic-content" style="display: none;">
            <!-- Module Status Section -->
            <div class="panel panel-default" style="margin-top: 20px;">
                <div class="panel-heading">
                    <h4 class="panel-title">Module Status</h4>
                </div>
                <div class="panel-body">
                    <table class="table table-striped" id="module-status-table">
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- Fraud Orders Section -->
            <div class="panel panel-default" style="margin-top: 20px;">
                <div class="panel-heading">
                    <h4 class="panel-title">Fraud Orders (Last 10)</h4>
                </div>
                <div class="panel-body">
                    <table class="table table-striped" id="fraud-orders-table" style="font-size: 0.9em;">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Client ID</th>
                                <th>Status</th>
                                <th>Created Date</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Tracked by Module</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    <div id="fraud-orders-empty" style="text-align: center; padding: 20px; color: #999;">
                        No fraud orders found
                    </div>
                </div>
            </div>

            <!-- Cron Hook Test Section -->
            <div class="panel panel-default" style="margin-top: 20px;">
                <div class="panel-heading">
                    <h4 class="panel-title">Cron Hook Test</h4>
                </div>
                <div class="panel-body">
                    <button class="btn btn-primary" id="test-cron-btn">Run Cron Test</button>
                    <div id="cron-test-result" style="margin-top: 20px; display: none;">
                        <div class="alert alert-info">
                            <pre id="cron-test-output" style="margin: 0;"></pre>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Instructions Section -->
            <div class="alert alert-info" style="margin-top: 20px;">
                <h4>How to Troubleshoot</h4>
                <ol>
                    <li>Enable "Enable debug logs" in module settings if not already enabled</li>
                    <li>Check "Module Status" above for any configuration issues</li>
                    <li>Create or find a fraud order and check if it appears in "Fraud Orders" section</li>
                    <li>Run the "Cron Hook Test" to verify WHMCS cron is calling the module</li>
                    <li>After running WHMCS cron, check module logs for "[AfterCronJob Hook]" entries</li>
                </ol>
            </div>
        </div>
    </div>

    {literal}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadDiagnostic();
            document.getElementById('test-cron-btn').addEventListener('click', testCron);
        });

        function loadDiagnostic() {
            Promise.all([
                fetch('/modules/addons/lkngatewaypreferences/api.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({a: 'diagnostic-module-status'})
                }),
                fetch('/modules/addons/lkngatewaypreferences/api.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({a: 'diagnostic-fraud-orders'})
                })
            ]).then(([statusRes, ordersRes]) => Promise.all([
                statusRes.json(),
                ordersRes.json()
            ])).then(([status, orders]) => {
                displayModuleStatus(status);
                displayFraudOrders(orders);
                document.getElementById('diagnostic-loading').style.display = 'none';
                document.getElementById('diagnostic-content').style.display = 'block';
            }).catch(err => {
                console.error('Error loading diagnostic:', err);
                document.getElementById('diagnostic-loading').innerHTML = 
                    '<div class="alert alert-danger">Error loading diagnostic information</div>';
            });
        }

        function displayModuleStatus(status) {
            const table = document.getElementById('module-status-table');
            const tbody = table.querySelector('tbody');
            tbody.innerHTML = '';

            for (const [key, value] of Object.entries(status)) {
                if (typeof value === 'object' && !Array.isArray(value)) {
                    for (const [subKey, subValue] of Object.entries(value)) {
                        const row = tbody.insertRow();
                        row.innerHTML = `
                            <td style="font-weight: bold;">${subKey}</td>
                            <td>${escapeHtml(JSON.stringify(subValue))}</td>
                        `;
                    }
                } else if (Array.isArray(value)) {
                    const row = tbody.insertRow();
                    row.innerHTML = `
                        <td style="font-weight: bold;">${key}</td>
                        <td><ul style="margin: 0; padding-left: 20px;">
                            ${value.map(v => `<li>${escapeHtml(v)}</li>`).join('')}
                        </ul></td>
                    `;
                }
            }
        }

        function displayFraudOrders(orders) {
            const table = document.getElementById('fraud-orders-table');
            const tbody = table.querySelector('tbody');
            const emptyDiv = document.getElementById('fraud-orders-empty');

            tbody.innerHTML = '';

            if (orders.error) {
                emptyDiv.textContent = 'Error: ' + orders.error;
                emptyDiv.style.display = 'block';
                return;
            }

            if (orders.length === 0) {
                emptyDiv.style.display = 'block';
                return;
            }

            emptyDiv.style.display = 'none';
            orders.forEach(order => {
                const row = tbody.insertRow();
                const trackedBadge = order.tracked_by_module === 'Yes' 
                    ? '<span class="label label-success">Yes</span>' 
                    : '<span class="label label-warning">No</span>';
                
                row.innerHTML = `
                    <td><strong>${order.order_id}</strong></td>
                    <td>${order.client_id}</td>
                    <td><span class="label label-danger">${order.status}</span></td>
                    <td>${order.created_date}</td>
                    <td>${order.amount}</td>
                    <td>${order.payment_method}</td>
                    <td>${trackedBadge}</td>
                `;
            });
        }

        function testCron() {
            const btn = document.getElementById('test-cron-btn');
            btn.disabled = true;
            btn.textContent = 'Running test...';

            fetch('/modules/addons/lkngatewaypreferences/api.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({a: 'diagnostic-test-cron'})
            }).then(res => res.json()).then(data => {
                const resultDiv = document.getElementById('cron-test-result');
                const output = document.getElementById('cron-test-output');
                output.textContent = JSON.stringify(data, null, 2);
                resultDiv.style.display = 'block';
                btn.disabled = false;
                btn.textContent = 'Run Cron Test';
            }).catch(err => {
                const resultDiv = document.getElementById('cron-test-result');
                const output = document.getElementById('cron-test-output');
                output.textContent = 'Error: ' + err.message;
                resultDiv.style.display = 'block';
                btn.disabled = false;
                btn.textContent = 'Run Cron Test';
            });
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
    {/literal}
{/block}
