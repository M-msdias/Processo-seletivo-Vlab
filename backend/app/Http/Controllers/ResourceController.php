<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreResourceRequest;
use App\Http\Requests\UpdateResourceRequest;
use App\Http\Resources\EducationalResourceResource;
use App\Models\EducationalResource;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Info(
 *     title="Hub Inteligente de Recursos Educacionais",
 *     version="1.0.0",
 *     description="API para gerenciamento de materiais didáticos com Smart Assist via IA"
 * )
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 */
class ResourceController extends Controller
{
    /**
     * @OA\Get(
     *     path="/resources",
     *     tags={"Resources"},
     *     summary="Listar todos os recursos educacionais",
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Itens por página",
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(response=200, description="Lista paginada de recursos")
     * )
    */
    public function index(): AnonymousResourceCollection
    {
         try {
            $perPage = request('per_page', 15);
            
            Log::info('Resource List Requested', [
                'per_page' => $perPage,
            ]);

            $resources = EducationalResource::with('tags')
                ->latest()
                ->paginate($perPage);

            Log::info('Resource List Retrieved', [
                'total' => $resources->total(),
                'per_page' => $resources->perPage(),
                'current_page' => $resources->currentPage()
            ]);

            return EducationalResourceResource::collection($resources);

        } catch (\Exception $e) {
            Log::error('Failed to list resources', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Erro ao listar recursos educacionais.'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/resources",
     *     tags={"Resources"},
     *     summary="Criar novo recurso educacional",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","type","url"},
     *             @OA\Property(property="title", type="string", example="Introdução à Álgebra Linear"),
     *             @OA\Property(property="description", type="string", example="Aula sobre vetores e matrizes"),
     *             @OA\Property(property="type", type="string", enum={"video","pdf","link"}),
     *             @OA\Property(property="url", type="string", example="https://youtube.com/exemplo"),
     *             @OA\Property(property="tags", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(response=201, description="Recurso criado com sucesso"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function store(StoreResourceRequest $request): EducationalResourceResource
    {
        try {
            $validatedData = $request->validated();
            
            Log::info('Attempting to create resource', [
                'title' => $validatedData['title'],
                'type' => $validatedData['type'],
                'has_tags' => isset($request->tags),
                'user_id' => auth()->id() ?? 'guest'
            ]);

            $resource = EducationalResource::create($validatedData);

            Log::info('Resource created successfully', [
                'resource_id' => $resource->id,
                'title' => $resource->title,
                'type' => $resource->type
            ]);

            if ($request->has('tags') && !empty($request->tags)) {
                $this->syncTags($resource, $request->tags);
                
                Log::info('Tags synced for resource', [
                    'resource_id' => $resource->id,
                    'tag_count' => count($request->tags)
                ]);
            }

            return new EducationalResourceResource($resource->load('tags'));

        } catch (ValidationException $e) {
            Log::warning('Resource creation validation failed', [
                'errors' => $e->errors(),
                'user_id' => auth()->id() ?? 'guest'
            ]);
            
            throw $e; 

        } catch (\Exception $e) {
            Log::error('Failed to create resource', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->except(['_token'])
            ]);

            return response()->json([
                'message' => 'Erro ao criar recurso educacional.'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/resources/{id}",
     *     tags={"Resources"},
     *     summary="Buscar recurso por ID",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Recurso encontrado"),
     *     @OA\Response(response=404, description="Recurso não encontrado")
     * )
     */
    public function show(EducationalResource $resource): EducationalResourceResource
    {
        try {
            Log::info('Resource details requested', [
                'resource_id' => $resource->id,
                'title' => $resource->title,
                'user_id' => auth()->id() ?? 'guest'
            ]);

            $resource->load('tags');

            Log::info('Resource details retrieved', [
                'resource_id' => $resource->id,
                'tag_count' => $resource->tags->count()
            ]);

            return new EducationalResourceResource($resource);

        } catch (ModelNotFoundException $e) {
            Log::warning('Resource not found', [
                'resource_id' => request()->route('resource')
            ]);
            
            throw $e;

        } catch (\Exception $e) {
            Log::error('Failed to retrieve resource', [
                'resource_id' => $resource->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Erro ao buscar recurso educacional.'
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/resources/{id}",
     *     tags={"Resources"},
     *     summary="Atualizar recurso educacional",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="type", type="string", enum={"video","pdf","link"}),
     *             @OA\Property(property="url", type="string"),
     *             @OA\Property(property="tags", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Recurso atualizado"),
     *     @OA\Response(response=404, description="Recurso não encontrado")
     * )
     */
    public function update(UpdateResourceRequest $request, EducationalResource $resource): EducationalResourceResource
    {
        try {
            $validatedData = $request->validated();
            
            Log::info('Attempting to update resource', [
                'resource_id' => $resource->id,
                'title' => $resource->title,
                'changes' => array_keys($validatedData),
                'user_id' => auth()->id() ?? 'guest'
            ]);

            $oldData = [
                'title' => $resource->title,
                'description' => $resource->description,
                'type' => $resource->type,
                'url' => $resource->url
            ];

            $resource->update($validatedData);

            Log::info('Resource updated successfully', [
                'resource_id' => $resource->id,
                'old_data' => $oldData,
                'new_data' => $validatedData
            ]);

            if ($request->has('tags')) {
                $oldTags = $resource->tags->pluck('name')->toArray();
                $this->syncTags($resource, $request->tags ?? []);
                
                Log::info('Tags updated for resource', [
                    'resource_id' => $resource->id,
                    'old_tags' => $oldTags,
                    'new_tags' => $request->tags,
                    'tag_count' => count($request->tags ?? [])
                ]);
            }

            return new EducationalResourceResource($resource->load('tags'));

        } catch (ModelNotFoundException $e) {
            Log::warning('Resource not found for update', [
                'resource_id' => request()->route('resource')
            ]);
            
            throw $e;

        } catch (ValidationException $e) {
            Log::warning('Resource update validation failed', [
                'resource_id' => $resource->id,
                'errors' => $e->errors()
            ]);
            
            throw $e;

        } catch (\Exception $e) {
            Log::error('Failed to update resource', [
                'resource_id' => $resource->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->except(['_token'])
            ]);

            return response()->json([
                'message' => 'Erro ao atualizar recurso educacional.'
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/resources/{id}",
     *     tags={"Resources"},
     *     summary="Remover recurso educacional",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Recurso removido"),
     *     @OA\Response(response=404, description="Recurso não encontrado")
     * )
     */
    public function destroy(EducationalResource $resource): JsonResponse
    {
        try {
            Log::info('Attempting to delete resource', [
                'resource_id' => $resource->id,
                'title' => $resource->title,
                'type' => $resource->type,
                'user_id' => auth()->id() ?? 'guest'
            ]);

            // Store data for log before deletion
            $resourceData = [
                'id' => $resource->id,
                'title' => $resource->title,
                'type' => $resource->type,
                'tags' => $resource->tags->pluck('name')->toArray()
            ];

            $resource->delete();

            Log::info('Resource deleted successfully', [
                'deleted_resource' => $resourceData,
                'user_id' => auth()->id() ?? 'guest'
            ]);

            return response()->json([
                'message' => 'Recurso removido com sucesso.',
                'deleted_id' => $resourceData['id']
            ]);

        } catch (ModelNotFoundException $e) {
            Log::warning('Resource not found for deletion', [
                'resource_id' => request()->route('resource')
            ]);
            
            throw $e;

        } catch (\Exception $e) {
            Log::error('Failed to delete resource', [
                'resource_id' => $resource->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Erro ao remover recurso educacional.'
            ], 500);
        }
    }

    private function syncTags(EducationalResource $resource, array $tagNames): void
    {
        try {
            $tagIds = collect($tagNames)->map(function (string $name) {
                return Tag::firstOrCreate(['name' => strtolower(trim($name))])->id;
            });

            $resource->tags()->sync($tagIds);

        } catch (\Exception $e) {
            Log::error('Failed to sync tags', [
                'resource_id' => $resource->id,
                'tags' => $tagNames,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
}
