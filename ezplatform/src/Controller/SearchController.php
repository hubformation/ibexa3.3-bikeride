<?php


namespace App\Controller;



use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\Pagination\Pagerfanta\AbstractSearchResultAdapter;
use eZ\Publish\Core\Pagination\Pagerfanta\ContentSearchAdapter;
use eZ\Publish\Core\Pagination\Pagerfanta\ContentSearchHitAdapter;
use eZ\Publish\Core\Pagination\Pagerfanta\Pagerfanta;
use eZ\Publish\Core\QueryType\QueryTypeRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

class SearchController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{

    /**
     * SearchController constructor.
     * @param SearchService $searchService
     */
    private $searchService;


    public function __construct(SearchService $searchService, QueryTypeRegistry $queryTypeRegistry)
    {
        $this->searchService = $searchService;
        $this->queryTypeRegistry = $queryTypeRegistry;
        $this->paginationLimit = 2;
    }

    public function bikeRideSearchPaginated(Request $request): Response
    {
        $queryType = $this->queryTypeRegistry->getQueryType('BikeRide');
        $searchText = $request->get('q');
        $query = $queryType->getQuery(['contentType' => 'ride', 'searchText' => $searchText]);
        $pager = new Pagerfanta(new ContentSearchHitAdapter($query, $this->searchService));
        // TODO put 2 as a parameter
        $pager->setMaxPerPage($this->paginationLimit);
        $pager->setCurrentPage(($request->get('page',1)));
        //dd($pager);
        return $this->render('@ezdesign/paginated_search.html.twig', [
            'bike_rides' => $pager,
            'totalItemCount' => $pager->getNbResults(),
            'search_text' => $searchText
        ]);
    }

    public function bikeRideSearch(Request $request): Response
    {
        $queryType = $this->queryTypeRegistry->getQueryType('BikeRide');
        //dd($queryType);
        $searchText = $request->get('q');
        $query = $queryType->getQuery(['contentType' => 'ride', 'searchText' => $searchText]);
        // important
        $results = $this->searchService->findContent($query);
        // dd($results);
        return $this->render('@ezdesign/search.html.twig', [
            'bike_rides' => $results,
            'search_text' => $searchText
        ]);
    }

    public function bikeRideSearchSimple(Request $request): Response
    {
        $searchText = $request->get('q');
        $criteria = [
            new Criterion\Visibility(Criterion\Visibility::VISIBLE),
            new Criterion\ContentTypeIdentifier(['ride'])
        ];
        if (!empty($searchText)) {
            $criteria[] = new Criterion\FullText($searchText);
        }
        $query = new Query([
            'filter' => new Criterion\LogicalAnd($criteria),
            'performCount' => false
        ]);
        // important
        $results = $this->searchService->findContent($query);
        // dd($results);
        return $this->render('@ezdesign/search.html.twig', [
            'bike_rides' => $results,
            'search_text' => $searchText
        ]);
    }
}