<?php

namespace App\Rest\Resources;

use Lomkit\Rest\Http\Resource;
use App\Models\Support;

class SupportResource extends Resource
{
    /**
     * The model associated with this resource.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    public static $model = Support::class;

    /**
     * The fields that should be available in API responses.
     */
    public function fields(): array
    {
        return [
            $this->field('id')->readOnly(),
            $this->field('client_id')->integer(),
            $this->field('email')->string()->searchable(),
            $this->field('description')->text(),
            $this->field('address')->string(),
            $this->field('created_at')->readOnly(),
            $this->field('updated_at')->readOnly(),
        ];
    }

    /**
     * The filters that can be applied to queries.
     */
    public function filters(): array
    {
        return [
            $this->filter('client_id')->integer(),
            $this->filter('email')->string(),
            $this->filter('address')->string(),
        ];
    }

    /**
     * The available actions for this resource.
     */
    public function actions(): array
    {
        return [
            $this->create(),
            $this->update(),
            $this->delete(),
        ];
    }
}
