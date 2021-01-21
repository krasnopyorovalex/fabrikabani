<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Project\Queries\GetProjectByIdQuery;
use App\Domain\ProjectImage\Commands\CreateProjectImageCommand;
use App\Domain\ProjectImage\Commands\DeleteProjectImageCommand;
use App\Domain\ProjectImage\Commands\UpdateProjectImageCommand;
use App\Domain\ProjectImage\Commands\UpdatePositionsProjectImageCommand;
use App\Domain\ProjectImage\Queries\GetProjectImageByIdQuery;
use Domain\ProjectImage\Requests\CreateProjectImageRequest;
use Domain\ProjectImage\Requests\UpdateProjectImageRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\UploadImagesService;

/**
 * Class ProjectImageController
 * @package App\Http\Controllers\Admin
 */
class ProjectImageController extends Controller
{
    /**
     * @var UploadImagesService
     */
    private $uploadGalleryImagesService;

    /**
     * GalleryImageController constructor.
     * @param UploadImagesService $uploadGalleryImagesService
     */
    public function __construct(UploadImagesService $uploadGalleryImagesService)
    {
        $this->uploadGalleryImagesService = $uploadGalleryImagesService;
    }

    /**
     * @param int $project
     * @return array
     * @throws \Throwable
     */
    public function index(int $project): array
    {
        $project = $this->dispatch(new GetProjectByIdQuery($project));

        return [
            'images' => view('admin.projects._images_box', [
                'project' => $project
            ])->render()
        ];
    }

    /**
     * @param CreateProjectImageRequest $request
     * @param $project
     * @return array
     */
    public function store(CreateProjectImageRequest $request, $project): array
    {
        $this->uploadGalleryImagesService->setHeightThumb(370);
        $this->uploadGalleryImagesService->setWidthThumb(370);

        $image = $this->uploadGalleryImagesService->upload($request, 'project', $project, true);
        $this->dispatch(new CreateProjectImageCommand($image));

        return [
            'message' => 'Данные сохранены успешно'
        ];
    }

    /**
     * @param $id
     * @return string
     * @throws \Throwable
     */
    public function edit($id): string
    {
        $image = $this->dispatch(new GetProjectImageByIdQuery($id));

        return view('admin.projects._image_popup', [
            'image' => $image
        ])->render();
    }

    /**
     * @param $id
     * @param UpdateProjectImageRequest $request
     * @return array
     * @throws \Throwable
     */
    public function update($id, UpdateProjectImageRequest $request): array
    {
        $this->dispatch(new UpdateProjectImageCommand($id, $request));
        $image = $this->dispatch(new GetProjectImageByIdQuery($id));

        return [
            'images' => view('admin.projects._images_box', [
                'project' => $image->project
            ])->render(),
            'message' => 'Данные сохранены успешно'
        ];
    }

    /**
     * @param $id
     * @return array
     */
    public function destroy($id): array
    {
        $this->dispatch(new DeleteProjectImageCommand($id));

        return [
            'message' => 'Изображение удалено успешно'
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function updatePositions(Request $request): array
    {
        $this->dispatch(new UpdatePositionsProjectImageCommand($request));

        return [
            'message' => 'Порядок изображений сохранён успешно'
        ];
    }
}
