<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roulette Test - 2660 Spins</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        button {
            background: #D1A14A;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 20px;
        }
        button:hover {
            background: #b88a3a;
        }
        button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        #progress {
            margin: 20px 0;
            font-size: 18px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #D1A14A;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .tier-rare { background-color: #d4edda; }
        .tier-medium { background-color: #e7d4f5; }
        .tier-common { background-color: #ffe5cc; }
        .summary {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .summary h3 {
            margin-top: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Roulette Distribution Test - 2660 Spins</h1>

        <button id="runTest" onclick="runTest()">Run 2660 Spins</button>
        <button id="reset" onclick="resetTest()" style="display:none;">Reset</button>

        <div id="progress"></div>

        <div class="summary" id="summary" style="display:none;">
            <h3>Tier Summary</h3>
            <div id="tierSummary"></div>
        </div>

        <table id="results" style="display:none;">
            <thead>
                <tr>
                    <th>Locker</th>
                    <th>Tier</th>
                    <th>Initial Allocation</th>
                    <th>Times Selected</th>
                    <th>Percentage</th>
                    <th>Expected %</th>
                    <th>Remaining</th>
                </tr>
            </thead>
            <tbody id="resultsBody">
            </tbody>
        </table>
    </div>

    <script>
        var products = @json($products);
        var results = {};
        var tierResults = {
            'rare': 0,
            'medium': 0,
            'common': 0
        };
        var totalSpins = 0;

        // Initialize results
        products.forEach(function(product, index) {
            results[index + 1] = {
                name: product.name,
                tier: product.tier,
                allocation: product.allocation,
                available: product.available,
                count: 0
            };
        });

        function spinRoulette() {
            // Step 1: Define tier percentages (fixed)
            var tierPercentages = {
                'rare': 10,
                'medium': 30,
                'common': 60
            };

            // Step 2: Select tier based on tier percentage
            var tierRandom = Math.random() * 100;
            var selectedTier;

            if (tierRandom < tierPercentages.rare) {
                selectedTier = 'rare';
            } else if (tierRandom < tierPercentages.rare + tierPercentages.medium) {
                selectedTier = 'medium';
            } else {
                selectedTier = 'common';
            }

            // Step 3: Filter products by selected tier that have available quantity
            var tierProducts = products.filter(function(product) {
                return product.tier === selectedTier && product.available > 0;
            });

            // If no products available in selected tier, try other tiers
            if (tierProducts.length === 0) {
                var tierOrder = ['common', 'medium', 'rare'];
                for (var i = 0; i < tierOrder.length; i++) {
                    if (tierOrder[i] !== selectedTier) {
                        tierProducts = products.filter(function(product) {
                            return product.tier === tierOrder[i] && product.available > 0;
                        });

                        if (tierProducts.length > 0) {
                            selectedTier = tierOrder[i];
                            break;
                        }
                    }
                }
            }

            if (tierProducts.length === 0) {
                return null; // No products available
            }

            // Step 4: Select product with highest available quantity in the tier
            var selectedProduct = tierProducts.reduce(function(max, product) {
                return product.available > max.available ? product : max;
            }, tierProducts[0]);

            // Update available quantity
            selectedProduct.available--;

            // Track tier selection
            tierResults[selectedTier]++;

            // Find the index (1-based) in the original products array
            var num = products.findIndex(function(p) {
                return p.id === selectedProduct.id;
            }) + 1;

            return num;
        }

        function runTest() {
            document.getElementById('runTest').disabled = true;
            document.getElementById('progress').textContent = 'Running test...';

            // Reset
            products.forEach(function(product) {
                product.available = product.allocation;
            });

            Object.keys(results).forEach(function(key) {
                results[key].count = 0;
                results[key].available = results[key].allocation;
            });

            tierResults = { 'rare': 0, 'medium': 0, 'common': 0 };
            totalSpins = 0;

            // Run 2660 spins
            for (var i = 0; i < 2660; i++) {
                var num = spinRoulette();
                if (num !== null) {
                    results[num].count++;
                    results[num].available--;
                    totalSpins++;
                }
            }

            displayResults();
        }

        function displayResults() {
            document.getElementById('progress').textContent = 'Test complete! Total spins: ' + totalSpins;
            document.getElementById('results').style.display = 'table';
            document.getElementById('summary').style.display = 'block';
            document.getElementById('reset').style.display = 'inline-block';

            var tbody = document.getElementById('resultsBody');
            tbody.innerHTML = '';

            Object.keys(results).forEach(function(key) {
                var result = results[key];
                var percentage = ((result.count / totalSpins) * 100).toFixed(2);
                var expected = ((result.allocation / 2660) * 100).toFixed(2);

                var row = document.createElement('tr');
                row.className = 'tier-' + result.tier;
                row.innerHTML =
                    '<td>Locker ' + result.name + '</td>' +
                    '<td>' + result.tier.toUpperCase() + '</td>' +
                    '<td>' + result.allocation + '</td>' +
                    '<td>' + result.count + '</td>' +
                    '<td>' + percentage + '%</td>' +
                    '<td>' + expected + '%</td>' +
                    '<td>' + result.available + '</td>';
                tbody.appendChild(row);
            });

            // Display tier summary
            var tierSummary = document.getElementById('tierSummary');
            var tierTotal = tierResults.rare + tierResults.medium + tierResults.common;
            tierSummary.innerHTML =
                '<p><strong>Rare Tier (Expected 10%):</strong> ' + tierResults.rare + ' spins (' + ((tierResults.rare / tierTotal) * 100).toFixed(2) + '%)</p>' +
                '<p><strong>Medium Tier (Expected 30%):</strong> ' + tierResults.medium + ' spins (' + ((tierResults.medium / tierTotal) * 100).toFixed(2) + '%)</p>' +
                '<p><strong>Common Tier (Expected 60%):</strong> ' + tierResults.common + ' spins (' + ((tierResults.common / tierTotal) * 100).toFixed(2) + '%)</p>';
        }

        function resetTest() {
            location.reload();
        }
    </script>
</body>
</html>
