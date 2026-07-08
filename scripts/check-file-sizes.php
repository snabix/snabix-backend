<?php

declare(strict_types=1);

$trackedFiles = [
    'app/CLI/SharedCLICleanupStorage.php' => [
        'maxLines' => 357,
        'decision' => 'decompose on next functional change or document why it remains large',
    ],
    'app/News/Filament/Resources/NewsPosts/Schemas/NewsPostForm.php' => [
        'maxLines' => 349,
        'decision' => 'decompose on next functional change or document why it remains large',
    ],
    'app/Listing/Infrastructure/Services/ListingAttributeValueSynchronizer.php' => [
        'maxLines' => 331,
        'decision' => 'decompose on next functional change or document why it remains large',
    ],
];

$root = dirname(__DIR__);
$failed = false;

foreach ($trackedFiles as $path => $rule) {
    $absolutePath = $root . DIRECTORY_SEPARATOR . $path;

    if (! is_file($absolutePath)) {
        fwrite(STDERR, sprintf("File size check failed: %s is missing.\n", $path));
        $failed = true;
        continue;
    }

    $contents = file_get_contents($absolutePath);

    if ($contents === false) {
        fwrite(STDERR, sprintf("File size check failed: %s cannot be read.\n", $path));
        $failed = true;
        continue;
    }

    $lineCount = substr_count($contents, "\n");

    if ($contents !== '' && ! str_ends_with($contents, "\n")) {
        ++$lineCount;
    }

    if ($lineCount > $rule['maxLines']) {
        fwrite(
            STDERR,
            sprintf(
                "%s has %d lines, baseline is %d. Please decompose it or update the baseline with a documented reason.\n",
                $path,
                $lineCount,
                $rule['maxLines'],
            ),
        );
        $failed = true;
        continue;
    }

    printf("%s: %d/%d lines (%s)\n", $path, $lineCount, $rule['maxLines'], $rule['decision']);
}

exit($failed ? 1 : 0);
