<?php


namespace App\QueryType;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Filter\Filter;
use \eZ\Publish\Core\QueryType\QueryType;

class BikeRideListQueryType implements QueryType
{

    /**
     * @inheritDoc
     */
    public function getQuery(array $parameters = [])
    {
        // https://doc.ibexa.co/en/latest/guide/content_rendering/queries_and_controllers/custom_query_type/#create-a-custom-query-type
        $criteria[] = new Query\Criterion\Visibility(Query\Criterion\Visibility::VISIBLE);
        $criteria[] = new Query\Criterion\ContentTypeIdentifier(['ride']);
        return new Query([
            'filter' => new Query\Criterion\LogicalAnd($criteria)
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getSupportedParameters()
    {
        // TODO: Implement getSupportedParameters() method.
    }

    /**
     * @inheritDoc
     */
    public static function getName()
    {
        return 'BikeRideList';
    }
}