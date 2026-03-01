<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreResourceRequest;
use App\Http\Requests\UpdateResourceRequest;
use App\Http\Resources\EducationalResourceResource;
use App\Models\EducationalResource;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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
        $resources = EducationalResource::with('tags')
            ->latest()
            ->paginate(request('per_page', 15));

        return EducationalResourceResource::collection($resources);
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
        $resource = EducationalResource::create($request->validated());

        $this->syncTags($resource, $request->tags ?? []);

        return new EducationalResourceResource($resource->load('tags'));
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
        return new EducationalResourceResource($resource->load('tags'));
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
        $resource->update($request->validated());

        if ($request->has('tags')) {
            $this->syncTags($resource, $request->tags ?? []);
        }

        return new EducationalResourceResource($resource->load('tags'));
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
