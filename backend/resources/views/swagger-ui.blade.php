<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation - Transaction Dashboard</title>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@5.18.3/swagger-ui.css" />
    <style>
        body {
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@5.18.3/swagger-ui-bundle.js" charset="UTF-8"></script>
    <script>
        window.onload = function() {
            const ui = SwaggerUIBundle({
                url: "/docs",
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis
                ],
                persistAuthorization: true
            });
            window.ui = ui;
        };
    </script>
</body>
</html>
