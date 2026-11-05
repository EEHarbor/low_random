<?php

/**
 * Inventory shipped ordinary PHP, archive members and the executable PHAR stub.
 *
 * @return array
 */
function lowRandomInventory()
{
    $root = dirname(__DIR__, 2);
    $sources = array();
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/low_random', FilesystemIterator::SKIP_DOTS));

    foreach ($iterator as $file) {
        if ($file->getExtension() === 'php') {
            $sources[substr($file->getPathname(), strlen($root) + 1)] = file_get_contents($file->getPathname());
        }
    }

    $archive = new Phar($root . '/low_random/low_random.phar');
    $prefix = 'phar://' . $root . '/low_random/low_random.phar/';

    foreach (new RecursiveIteratorIterator($archive) as $file) {
        if (substr($file->getFilename(), -4) === '.php') {
            $sources['low_random/low_random.phar!/' . substr($file->getPathname(), strlen($prefix))] = $file->getContent();
        }
    }

    $sources['low_random/low_random.phar!stub'] = $archive->getStub();
    ksort($sources);
    $inventory = array();

    foreach ($sources as $path => $source) {
        $tokens = token_get_all($source);
        $functions = array();

        foreach ($tokens as $index => $token) {
            if (!is_array($token) || $token[0] !== T_FUNCTION) {
                continue;
            }

            $name = '{closure}';
            $line = $token[2];

            for ($next = $index + 1; $next < count($tokens); $next++) {
                if (is_array($tokens[$next]) && $tokens[$next][0] === T_STRING) {
                    $name = $tokens[$next][1];
                    break;
                }

                if ($tokens[$next] === '(') {
                    break;
                }
            }

            $functions[] = array('name' => $name, 'line' => $line);
        }

        $inventory[$path] = array('sha256' => hash('sha256', $source), 'bytes' => strlen($source), 'lines' => substr_count($source, "\n") + 1, 'functions' => $functions);
    }

    return array('production_baseline' => '892b6dae8282b159e580e3c1a6076a39d9902f84', 'archive_sha256' => hash_file('sha256', $root . '/low_random/low_random.phar'), 'sources' => $inventory);
}

if (realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__) {
    echo json_encode(lowRandomInventory(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
}
