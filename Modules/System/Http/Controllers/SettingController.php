<?php

namespace Modules\System\Http\Controllers;

use App\Http\Controllers\Controller;
use Closure;
use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route as RouteFacade;
use ReflectionFunction;

class SettingController extends Controller
{
    public function index()
    {
        return view('System::pages.settings.index');
    }
    public function profile()
    {
        return view('System::pages.settings.profile');
    }
    public function modules()
    {
        $routes = $this->getModuleGetRoutes();

        return view('System::pages.settings.modules', [
            'routes' => $routes,
            'routeModules' => $routes->pluck('module')->unique()->sort()->values(),
        ]);
    }

    /**
     * Get registered GET routes whose controller or route file belongs to Modules.
     */
    private function getModuleGetRoutes(): Collection
    {
        return collect(RouteFacade::getRoutes()->getRoutes())
            ->filter(fn (Route $route) => in_array('GET', $route->methods(), true))
            ->map(function (Route $route) {
                $action = $route->getAction('uses');
                $actionName = $route->getActionName();
                $module = $this->moduleNameFromAction($action);

                if (!$module) {
                    return null;
                }

                return [
                    'module' => $module,
                    'uri' => $route->uri(),
                    'name' => $route->getName(),
                    'action' => $actionName === 'Closure' ? 'Closure' : $actionName,
                    'middleware' => array_values($route->gatherMiddleware()),
                    'domain' => $route->getDomain(),
                ];
            })
            ->filter()
            ->sortBy([
                ['module', 'asc'],
                ['uri', 'asc'],
            ])
            ->values();
    }

    private function moduleNameFromAction(mixed $action): ?string
    {
        if (is_string($action) && preg_match('/^Modules\\\\([^\\\\]+)/', $action, $matches)) {
            return $matches[1];
        }

        if (is_array($action) && isset($action[0])) {
            $controller = is_object($action[0]) ? get_class($action[0]) : $action[0];

            if (is_string($controller) && preg_match('/^Modules\\\\([^\\\\]+)/', $controller, $matches)) {
                return $matches[1];
            }
        }

        if ($action instanceof Closure) {
            $file = (new ReflectionFunction($action))->getFileName();
            $modulesPath = str_replace('\\', '/', base_path('Modules')).'/';
            $normalizedFile = str_replace('\\', '/', $file ?: '');

            if (str_starts_with($normalizedFile, $modulesPath)) {
                $relativePath = substr($normalizedFile, strlen($modulesPath));

                return explode('/', $relativePath, 2)[0] ?: null;
            }
        }

        return null;
    }
}
