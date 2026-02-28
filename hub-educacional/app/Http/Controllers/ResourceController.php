<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreResourceRequest;
use App\Http\Requests\UpdateResourceRequest;
use App\Http\Resources\EducationalResourceResource;
use App\Models\EducationalResource;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ResourceController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $resources = EducationalResource::with('tags')
            ->latest()
            ->paginate(request('per_page', 15));

        return EducationalResourceResource::collection($resources);
    }

    public function store(StoreResourceRequest $request): EducationalResourceResource
    {
        $resource = EducationalResource::create($request->validated());

        $this->syncTags($resource, $request->tags ?? []);

        return new EducationalResourceResource($resource->load('tags'));
    }

    public function show(EducationalResource $resource): EducationalResourceResource
    {
        return new EducationalResourceResource($resource->load('tags'));
    }

    public function update(UpdateResourceRequest $request, EducationalResource $resource): EducationalResourceResource
    {
        $resource->update($request->validated());

        if ($request->has('tags')) {
            $this->syncTags($resource, $request->tags ?? []);
        }

        return new EducationalResourceResource($resource->load('tags'));
    }

    public function destroy(EducationalResource $resource): JsonResponse
    {
        $resource->delete();

        return response()->json(['message' => 'Recurso removido com sucesso.']);
    }

    private function syncTags(EducationalResource $resource, array $tagNames): void
    {
        $tagIds = collect($tagNames)->map(function (string $name) {
            return Tag::firstOrCreate(['name' => strtolower(trim($name))])->id;
        });

        $resource->tags()->sync($tagIds);
    }
}
