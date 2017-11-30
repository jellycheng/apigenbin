<?php

/*
	1. cd apigen
	2. php bin.php
*/
$configFile = __DIR__ . '/.env';
$configData = array();
if(file_exists($configFile)) {
	$configData = parse_ini_string(file_get_contents($configFile));
	if(isset($configData['source'])) {
		$source = '';
		foreach((array)$configData['source'] as $dirK=>$dirV) {
			if(is_dir($dirV)) {
				$source .= '  - ' . $dirV . PHP_EOL;
			}
		
		}
	}
} else {
	exit($configFile . " file not exists");
}

echo "scan dir: " . PHP_EOL . $source . PHP_EOL;
if(isset($configData['doc_save_dir']) && $configData['doc_save_dir']) {
	$docDir = $configData['doc_save_dir'];
} else {
	exit("document don't save path");
}
if(function_exists('buildApiGenDoc_rmdir')) {
	buildApiGenDoc_rmdir($docDir);
}

if(!is_dir($docDir)) {
	@mkdir($docDir, 0777, true);
}
echo 'file save path：' . $docDir . PHP_EOL;
$title = isset($configData['doc_title'])?$configData['doc_title']:'api接口文档';

$config = <<<CONFIGYAML
title: {$title}
extensions:
  - php
source:
{$source}

destination: {$docDir}

accessLevels:
  - public
templateTheme: default
groups: auto
noSourceCode: true
internal: true
php: false
tree: false
deprecated: true
todo: true
download: false
exclude:
 - JellyTest.php

CONFIGYAML;


$configFile = __DIR__ . '/phpdocgen.yaml';
file_put_contents($configFile, $config);
$cliContent = '';
$apigen = __DIR__ . "/apigen.phar";
$handle = popen('php ' . $apigen . ' generate --config=' . $configFile . ' 2>&1', 'r');
while (!feof($handle)) {
	$cliContent .= fread($handle, 2096);
}
pclose($handle);
@unlink($configFile);
echo $cliContent . PHP_EOL;

