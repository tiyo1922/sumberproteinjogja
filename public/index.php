<?php

define('LARAVEL_START', microtime(true));

// Maintenance mode check
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// If Laravel vendor composer autoload exists, run standard Laravel application flow
if (file_exists(__DIR__.'/../vendor/autoload.php')) {
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $app->handleRequest(Illuminate\Http\Request::capture());
    exit;
}

// --------------------------------------------------------------------------
// High-Fidelity Standalone Blade Engine for Laravel Prototype
// --------------------------------------------------------------------------

if (!function_exists('asset')) {
    function asset($path) {
        $cleanPath = ltrim($path, '/');
        $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        if ($scriptDir === '/' || $scriptDir === '\\' || empty($scriptDir) || $scriptDir === '.') {
            return '/' . $cleanPath;
        }
        return rtrim($scriptDir, '/\\') . '/' . $cleanPath;
    }
}

class SimpleBladeEngine
{
    private $viewDir;

    public function __construct($viewDir)
    {
        $this->viewDir = rtrim($viewDir, '/\\') . '/';
    }

    public function render($viewName, $data = [])
    {
        $file = $this->viewDir . str_replace('.', '/', $viewName) . '.blade.php';
        if (!file_exists($file)) {
            throw new Exception("View [{$viewName}] not found.");
        }

        $raw = file_get_contents($file);

        // Step 1: Process @extends and @section
        if (preg_match('/@extends\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/', $raw, $extMatch)) {
            $layoutName = $extMatch[1];
            $layoutFile = $this->viewDir . str_replace('.', '/', $layoutName) . '.blade.php';
            $layoutContent = file_get_contents($layoutFile);

            // Extract sections
            preg_match_all('/@section\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)(.*?)@endsection/s', $raw, $secMatches, PREG_SET_ORDER);
            foreach ($secMatches as $s) {
                $secName = $s[1];
                $secBody = $s[2];
                $layoutContent = preg_replace('/@yield\s*\(\s*[\'"]' . preg_quote($secName, '/') . '[\'"]\s*\)/', $secBody, $layoutContent);
            }
            $raw = $layoutContent;
        }

        // Step 2: Recursively inline @include
        $maxDepth = 10;
        while (preg_match('/@include\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/', $raw) && $maxDepth > 0) {
            $raw = preg_replace_callback('/@include\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)/', function ($m) {
                $incFile = $this->viewDir . str_replace('.', '/', $m[1]) . '.blade.php';
                return file_exists($incFile) ? file_get_contents($incFile) : '';
            }, $raw);
            $maxDepth--;
        }

        // Step 3: Replace Blade Directives with PHP
        $compiled = $raw;

        // @foreach & @endforeach
        $compiled = preg_replace_callback('/@foreach\s*\((.*)\)/m', function($m) {
            return '<?php foreach(' . $m[1] . '): ?>';
        }, $compiled);
        $compiled = preg_replace('/@endforeach/', '<?php endforeach; ?>', $compiled);

        // @for & @endfor
        $compiled = preg_replace_callback('/@for\s*\((.*)\)/m', function($m) {
            return '<?php for(' . $m[1] . '): ?>';
        }, $compiled);
        $compiled = preg_replace('/@endfor/', '<?php endfor; ?>', $compiled);

        // @if, @elseif, @else, @endif
        $compiled = preg_replace_callback('/@if\s*\((.*)\)/m', function($m) {
            return '<?php if(' . $m[1] . '): ?>';
        }, $compiled);
        $compiled = preg_replace_callback('/@elseif\s*\((.*)\)/m', function($m) {
            return '<?php elseif(' . $m[1] . '): ?>';
        }, $compiled);
        $compiled = preg_replace('/@else/', '<?php else: ?>', $compiled);
        $compiled = preg_replace('/@endif/', '<?php endif; ?>', $compiled);

        // Raw echo {!! $var !!}
        $compiled = preg_replace('/\{\!!\s*(.+?)\s*\!!\}/s', '<?php echo ($1); ?>', $compiled);

        // Escaped echo {{ $var }}
        $compiled = preg_replace('/\{\{\s*(.+?)\s*\}\}/s', '<?php echo htmlspecialchars((string)($1 ?? ""), ENT_QUOTES, "UTF-8"); ?>', $compiled);

        // Step 4: Execute within isolated data scope
        extract($data);
        ob_start();
        eval('?>' . $compiled);
        return ob_get_clean();
    }
}

function view($viewName, $data = []) {
    $engine = new SimpleBladeEngine(dirname(__DIR__) . '/resources/views');
    echo $engine->render($viewName, $data);
}

require_once __DIR__.'/../app/Http/Controllers/LandingController.php';

$controller = new \App\Http\Controllers\LandingController();
$controller->index();
