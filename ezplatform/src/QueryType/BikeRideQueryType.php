<?php


namespace App\QueryType;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\ValueObject;
use \eZ\Publish\Core\QueryType\QueryType;

class BikeRideQueryType implements \eZ\Publish\Core\QueryType\QueryType
{

    /**
     * @inheritDoc
     */
    public function getQuery(array $parameters = [])
    {
        // https://doc.ibexa.co/en/latest/guide/content_rendering/queries_and_controllers/custom_query_type/#create-a-custom-query-type
        $criteria = [
            new Criterion\Visibility(Criterion\Visibility::VISIBLE),
            new Criterion\ContentTypeIdentifier($parameters['contentType']),
        ];
        if (!empty($parameters['parentLocationId'])) {
            $criteria[] = new Query\Criterion\ParentLocationId($parameters['parentLocationId']);
        }
        $searchText = $parameters['searchText']?? '';
        if (!empty($searchText)) {
            $criteria[] = new Criterion\FullText($searchText);
        }
        return new Query([
            'filter' => new Criterion\LogicalAnd($criteria),
            'performCount' => true
        ]);
        // if use paginationcontroller, we need the total count, so we say true
    }

    /**
     * @inheritDoc
     */
    public function getSupportedParameters()
    {
        return ['contentType', 'parentLocationId', 'searchText'];
    }

    /**
     * @inheritDoc
     */
    public static function getName()
    {
        return 'BikeRide';
    }
}