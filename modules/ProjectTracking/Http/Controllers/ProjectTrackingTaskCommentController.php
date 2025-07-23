<?php

namespace Modules\ProjectTracking\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Modules\ProjectTracking\Models\ProjectTrackingTaskComment;

/**
 * @class ProjectTrackingTaskCommentController
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingTaskCommentController extends Controller
{
    /**
     * [descripción del método]
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function index()
    {
        return view('projecttracking::index');
    }

    /**
     * [descripción del método]
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function create()
    {
        return view('projecttracking::create');
    }

    /**
     * [descripción del método]
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     object    Request    $request    Objeto con información de la petición
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            ProjectTrackingTaskComment::create([
                'task_comment_id' => $request->task_comment_id,
                'user_id' => auth()->user()->id,
                'comment' => $request->comment
            ]);
        });

        return response()->json(['result' => true], 200);
    }

    /**
     * [descripción del método]
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function show($id)
    {
        return view('projecttracking::show');
    }

    /**
     * [descripción del método]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function edit($id)
    {
        return view('projecttracking::edit');
    }

    /**
     * [descripción del método]
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     object    Request    $request         Objeto con datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function update(Request $request, $id)
    {
        $task_comment = ProjectTrackingTaskComment::find($request->input('id'));

        $task_comment->comment = $request->input('comment');
        $task_comment->save();

        return response()->json(['result' => true, 'message' => 'Comentario editado con éxito'], 200);
    }

    /**
     * [descripción del método]
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Retorna un json con todos los comentarios de un id en específico de una tarea
     *
     * @method getTaskComments
     *
     * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
     *
     * @return Renderable    [descripción de los datos devueltos]
     */
    public function getTaskComments(Request $request): JsonResponse
    {
        $query = ProjectTrackingTaskComment::query()
            ->where('task_comment_id', $request->task_comment_id)
            ->orderBy('id', 'asc')
            ->get();

        $comments = $query->map(function ($comment) {
            return [
                'id' => $comment->id,
                'user_id' => $comment->user_id,
                'user_name' => User::find($comment->user_id)?->name,
                'date' => $comment->created_at,
                'comment' => $comment->comment,
            ];
        });

        return response()->json($comments, JsonResponse::HTTP_OK);
    }
}
