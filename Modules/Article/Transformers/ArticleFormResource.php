<?php

namespace Modules\Article\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Modules\Article\Services\ArticleService;

class ArticleFormResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'           => $this->id,
            'date'         => $this->date ? $this->date->toDateTimeString() : now()->toDateTimeString(),
            'date_from'    => $this->date_from ? $this->date_from->toDateTimeString() : null,
            'date_to'      => $this->date_to ? $this->date_to->toDateTimeString() : null,
            'discount'     => $this->discount,
            'old_image'    => $this->image,
            'image'        => $this->oldImage(),
            'icon'         => $this->icon,
            'socials'      => $this->socials,
            'priority'     => $this->priority ?? 0,
            'type'         => $this->type,
            'no_show_home' => $this->no_show_home,
            'items'        => $this->getItems()
        ];
    }

    private function oldImage()
    {
        $old_image = null;
        if ($this->image) {
            $old_image = (new ArticleService())->linkImage($this->image, null, true);
        }

        return $old_image;
    }

    private function getItems(): Collection
    {
        $items = $this->emptyItems();

        if ($this->getTrans()->exists()) {
            $existItems = $this->getTrans->keyBy('lang_id');

            $items = array_replace($items, $existItems->toArray());
        }

        return collect($items);
    }

    private function emptyItems(): array
    {
        $items = [];
        foreach (config('app.locales') as $lang_id => $locale) {
            $items[$lang_id] = [
                'title'            => '',
                'description'      => '',
                'short_desc'       => '',
                'slug'             => '',
                'meta_title'       => '',
                'meta_description' => '',
                'meta_keywords'    => '',
                'active'           => 0,
                'lang_id'          => '',
            ];
        }

        return $items;
    }
}
