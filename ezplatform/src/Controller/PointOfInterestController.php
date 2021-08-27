<?php


namespace App\Controller;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Values\Content\Relation;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PointOfInterestController extends AbstractController
{
    /** @var ContentService */
    private $contentService;

    public function __construct(ContentService $contentService)
    {
        $this->contentService = $contentService;
    }

    /**
     * Add 'bike_ride' reverse relations to default view as a `rides` variable accessible from template.
     */
    public function pointOfInterestView(ContentView $view): ContentView
    {
        $rides = [];
        $contentId = $view->getContent()->id;
        $contentInfo = $this->contentService->loadContentInfo($contentId);
        $versionInfo = $this->contentService->loadVersionInfo($contentInfo);
        $relations = $this->contentService->loadReverseRelations($contentInfo);

        foreach ($relations as $relation) {
            $name = $relation->sourceContentInfo->name;
            $rides[] = ['name'=>$name, 'contentInfo'=>$relation->sourceContentInfo];
        }
        $view->addParameters(['rides' =>$rides, 'contentId' => $contentId, 'versionInfo' => $versionInfo, 'contentInfo'=>$contentInfo, 'relations'=>$relations]);
        return $view;
    }
}