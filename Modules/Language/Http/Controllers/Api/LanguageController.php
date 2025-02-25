<?php

namespace Modules\Language\Http\Controllers\Api;

use App\Http\Controllers\AdminController;
use Fynduck\FilesUpload\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Language\Entities\Language;
use Modules\Language\Http\Requests\StoreLanguageRequest;
use Modules\Language\Transformers\LanguageListResource;

class LanguageController extends AdminController
{
    public function __construct()
    {
        $this->middleware('admin:view');
    }

    /**
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $languages = Language::filter($request)
            ->paginate(30);

        return LanguageListResource::collection($languages);
    }

    /**
     * @param StoreLanguageRequest $request
     * @return bool
     */
    public function store(StoreLanguageRequest $request)
    {
        $image = null;
        if ($request->get('image')) {
            $image = UploadFile::file($request->get('image'))
                ->setFolder(Language::FOLDER_IMG)
                ->setName($request->get('name'))
                ->save();
        }

        $defaultLang = Language::where('default', 1)->where('active', 1)->value('slug');

        if (!File::exists(resource_path('lang/' . $request->get('slug')))) {
            File::copyDirectory(resource_path('lang/' . $defaultLang), resource_path('lang/' . $request->get('slug')));
        }

        Language::create(
            [
                'name'        => $request->get('name'),
                'country_iso' => $request->get('country_iso'),
                'slug'        => $request->get('slug'),
                'active'      => $request->get('active') ?? 0,
                'default'     => $request->get('default') ?? 0,
                'priority'    => $request->get('priority') ?? 0,
                'image'       => $image
            ]
        );

        return true;
    }

    /**
     * @param $id
     * @return LanguageListResource
     */
    public function show($id)
    {
        $item = Language::find($id);

        if (!$item) {
            $item = new Language();
        }

        return (new LanguageListResource($item));
    }

    public function update(StoreLanguageRequest $request, $id)
    {
        $language = Language::findOrFail($id);

        $image = null;
        if ($request->get('image')) {
            if (!Str::contains($request->get('image'), Language::FOLDER_IMG)) {
                $image = UploadFile::file($request->get('image'))
                    ->setFolder(Language::FOLDER_IMG)
                    ->setName($request->get('name'))
                    ->setOverwrite($language->image)
                    ->save();
            }
        }

        if ($language->slug != $request->get('slug') && !File::exists(resource_path('lang/' . $request->get('slug')))) {
            File::moveDirectory(resource_path('lang/' . $language->slug), resource_path('lang/' . $request->get('slug')));
        }

        $language->name = $request->get('name');
        $language->country_iso = $request->get('country_iso');
        $language->slug = $request->get('slug');
        $language->active = $request->get('active');
        $language->default = $request->get('default');
        $language->priority = $request->get('priority');
        if ($image) {
            $language->image = $image;
        }

        $language->save();

        return true;
    }

    public function destroy(Request $request, Language $language)
    {
        Storage::disk('public')->delete(Language::FOLDER_IMG . '/' . $language->image);

        $language->image = null;

        if (!$request->get('image')) {
            $language->active = false;
        }

        $language->save();

        return true;
    }
}
