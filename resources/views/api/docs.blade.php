<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forlled API Docs</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui.css">
    <style>
        html {
            box-sizing: border-box;
            overflow-y: scroll;
        }

        *, *::before, *::after {
            box-sizing: inherit;
        }

        body {
            margin: 0;
            background: #f6f3ef;
        }

        .topbar {
            display: none;
        }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>

    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
    <script>
        window.addEventListener('load', function () {
            window.SwaggerUIBundle({
                url: '{{ route('api.openapi') }}',
                dom_id: '#swagger-ui',
                deepLinking: true,
                docExpansion: 'list',
                displayRequestDuration: true,
            });
        });
    </script>
</body>
</html>
