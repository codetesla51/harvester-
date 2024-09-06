<?php
system(PHP_OS_FAMILY == "Windows" ? "cls" : "clear");
system('toilet -f standard -F gay "harvester"');

if (php_sapi_name() !== "cli") {
  die("\033[31mTool Must Be Run From Command Line\n\033[0m");
}

function displayProgressBar($total, $current)
{
  $percentage = ($current / $total) * 100;
  $bar = str_repeat("#", $current) . str_repeat("-", $total - $current);
  echo "\033[33m[{$bar}] " . round($percentage) . "%\r\033[0m";
}

echo "\033[33mEnter Website URL: \033[0m";
$url = trim(fgets(STDIN));

if (!filter_var($url, FILTER_VALIDATE_URL)) {
  die("\033[31mInvalid Url\n\033[0m");
}

echo "\033[33mFetching website content...\033[0m\n";
$start = time();
$html = @file_get_contents($url);
if ($html === false) {
  die("\033[31mFailed to fetch website content.\n\033[0m");
}
$duration = time() - $start;
echo "\033[32mFetching completed in {$duration} seconds.\n\033[0m";

$outputDir = "temp_output_" . uniqid();
mkdir($outputDir, 0755, true);
$htmlFile = $outputDir . "/index.html";
file_put_contents($htmlFile, $html);

$dom = new DOMDocument();
@$dom->loadHTML($html);
$xpath = new DOMXPath($dom);
$baseUrl = get_base_url($url);

$images = $xpath->query("//img/@src");
$imageCount = $images->length;
echo "\033[33mFetching images...\033[0m\n";
foreach ($images as $i => $img) {
  $imgUrl = resolve_url($baseUrl, $img->value);
  save_asset($imgUrl, $outputDir);
  displayProgressBar($imageCount, $i + 1);
}
echo "\n\033[32mImages fetched successfully.\n\033[0m";

$stylesheets = $xpath->query("//link[@rel='stylesheet']/@href");
$cssCount = $stylesheets->length;
echo "\033[33mFetching CSS files...\033[0m\n";
foreach ($stylesheets as $i => $stylesheet) {
  $cssUrl = resolve_url($baseUrl, $stylesheet->value);
  save_asset($cssUrl, $outputDir);
  displayProgressBar($cssCount, $i + 1);
}
echo "\n\033[32mCSS files fetched successfully.\n\033[0m";

$scripts = $xpath->query("//script/@src");
$jsCount = $scripts->length;
echo "\033[33mFetching JS files...\033[0m\n";
foreach ($scripts as $i => $script) {
  $jsUrl = resolve_url($baseUrl, $script->value);
  save_asset($jsUrl, $outputDir);
  displayProgressBar($jsCount, $i + 1);
}
echo "\n\033[32mJS files fetched successfully.\n\033[0m";

$downloadDir = getenv("HOME") ;
if (!is_dir($downloadDir)) {
  die("\033[31mDownloads folder not found.\n\033[0m");
}

$zipFile = $downloadDir . "/website_" . uniqid() . ".zip";
echo "\033[33mCreating ZIP file...\033[0m";
create_zip($outputDir, $zipFile);
echo "\033[32m ZIP file created successfully in $downloadDir.\n\033[0m";

delete_directory($outputDir);

echo "\033[32mWebsite content saved to $zipFile\n\033[0m";

function get_base_url($url)
{
  $parsedUrl = parse_url($url);
  return $parsedUrl["scheme"] .
    "://" .
    $parsedUrl["host"] .
    rtrim(dirname($parsedUrl["path"]), "/") .
    "/";
}

function resolve_url($baseUrl, $relativeUrl)
{
  if (parse_url($relativeUrl, PHP_URL_SCHEME) != "") {
    return $relativeUrl;
  }
  return $baseUrl . ltrim($relativeUrl, "/");
}

function save_asset($url, $outputDir)
{
  $content = @file_get_contents($url);
  if ($content === false) {
    return;
  }

  $parsedUrl = parse_url($url);
  $pathParts = pathinfo($parsedUrl["path"]);

  $assetDir = $outputDir . "/" . $pathParts["dirname"];
  if (!is_dir($assetDir)) {
    mkdir($assetDir, 0755, true);
  }

  $filename = $pathParts["basename"];
  file_put_contents($assetDir . "/" . $filename, $content);
}

function create_zip($sourceDir, $zipFile)
{
  $zip = new ZipArchive();
  if ($zip->open($zipFile, ZipArchive::CREATE) === true) {
    $sourceDir = realpath($sourceDir);
    $files = new RecursiveIteratorIterator(
      new RecursiveDirectoryIterator($sourceDir),
      RecursiveIteratorIterator::LEAVES_ONLY
    );
    foreach ($files as $name => $file) {
      if (!$file->isDir()) {
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($sourceDir) + 1);
        $zip->addFile($filePath, $relativePath);
      }
    }
    $zip->close();
  } else {
    die("\033[31mFailed to create ZIP file.\n\033[0m");
  }
}

function delete_directory($dir)
{
  if (!is_dir($dir)) {
    return;
  }
  $files = array_diff(scandir($dir), [".", ".."]);
  foreach ($files as $file) {
    is_dir("$dir/$file")
      ? delete_directory("$dir/$file")
      : unlink("$dir/$file");
  }
  rmdir($dir);
}
?>
