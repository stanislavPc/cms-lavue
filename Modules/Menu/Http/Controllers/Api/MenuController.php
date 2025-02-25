<?php

namespace Modules\Menu\Http\Controllers\Api;

use App\Http\Controllers\AdminController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Modules\Language\Entities\Language;
use Modules\Menu\Entities\Menu;
use Modules\Menu\Entities\MenuSettings;
use Modules\Menu\Http\Requests\MenuValidate;
use Modules\Menu\Services\MenuService;
use Modules\Menu\Transformers\MenuFormResource;
use Modules\Menu\Transformers\MenuListResource;

class MenuController extends AdminController
{
    protected $targets = [];

    protected $positions = [];

    protected $parents = [];

    protected $menuService;

    public function __construct(MenuService $menuService)
    {
        $this->middleware('admin:view');

        $this->positions = Menu::positions();

        $this->parents = Menu::leftJoin('menu_trans', 'menus.id', '=', 'menu_trans.menu_id')
            ->where('lang_id', config('app.locale_id'))
            ->get(['title', 'menus.id', 'position'])
            ->groupBy('position');

        $this->targets = Menu::targets();

        $this->menuService = $menuService;
    }

    /**
     * Lists menu for position
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $menu = Menu::leftJoin('menu_trans', 'menus.id', '=', 'menu_trans.menu_id')
            ->select('menus.*', 'menu_trans.title', 'menu_trans.lang_id', 'menu_trans.active')
            ->filter($request)->paginate(25);

        $additional = [
            'languages' => Language::whereActive(1)->pluck('name', 'id'),
            'settings'  => $this->menuService->settings()
        ];

        return MenuListResource::collection($menu)->additional($additional);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param MenuValidate $request
     * @return bool
     * @throws \Exception
     */
    public function store(MenuValidate $request): bool
    {
        $nameImages = $this->menuService->saveImages($request);

        /**
         * Create menu
         */
        DB::beginTransaction();
        $menu = $this->menuService->addUpdate($request, $nameImages);

        $this->menuService->addUpdateTrans($menu, $request->get('items'));

        if ($request->get('show_page')) {
            $this->menuService->showOn($menu->id, $request->get('show_page'));
        }

        DB::commit();

        return true;
    }

    /**
     * @param $id
     * @return MenuFormResource
     */
    public function show($id): MenuFormResource
    {
        $item = Menu::find($id);

        $additional = [
            'positions' => $this->positions,
            'parents'   => $this->parents,
            'targets'   => $this->targets
        ];

        if (!$item) {
            $item = new Menu();
        }

        return (new MenuFormResource($item))->additional($additional);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param MenuValidate $request
     * @param Menu $menu
     * @return bool
     */
    public function update(MenuValidate $request, Menu $menu): bool
    {
        $nameImages = $this->menuService->saveImages($request);

        /**
         * Update menu
         */
        DB::beginTransaction();
        $menu = $this->menuService->addUpdate($request, $nameImages, $menu->id);

        $this->menuService->addUpdateTrans($menu, $request->get('items'));

        $this->menuService->showOn($menu->id, $request->get('show_page'));
        DB::commit();

        return true;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Menu $menu
     * @param Request $request
     * @return bool
     * @throws \Exception
     */
    public function destroy(Menu $menu, Request $request): bool
    {
        if ($request->get('image')) {
            $this->menuService->deleteImages($menu->image);
            $this->menuService->deleteOriginalImage($menu->image);
            $menu->image = '';
            $menu->save();
        } else {
            return $menu->delete();
        }

        return true;
    }

    /**
     * Save menus settings
     * @param Request $request
     * @return JsonResponse
     */
    public function saveSettings(Request $request): bool
    {
        $data = $this->menuService->prepareSizeSettingsToSave($request);

        MenuSettings::updateOrCreate(
            [
                'name' => 'sizes',
            ],
            [
                'data' => $data
            ]
        );

        return true;
    }
}
