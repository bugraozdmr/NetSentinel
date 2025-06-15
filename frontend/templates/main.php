<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>
        <?php echo $appName; ?>
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php
    include __DIR__ . '/../components/header.php'; ?>
</head>

<body>
    <div class="flex flex-col min-h-screen bg-gray-100">
        <?php include __DIR__ . '/../components/navbar.php'; ?>

        <main class="flex-1 p-6">
            <?= $content ?>
        </main>

    </div>

    <?php include __DIR__ . '/../components/footer.php'; ?>
    <?php include __DIR__ . '/../components/scripts.php'; ?>
</body>

</html>