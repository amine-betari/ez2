<?php

namespace AppBundle\Controller;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\MVC\Symfony\Controller\Controller;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\Pagination\Pagerfanta\ContentSearchAdapter;

use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;

class HomepageController extends Controller
{
    public function getAllRidesAction(Request $request)
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();
        //$contentService = $repository->getContentService();
        $rootLocationId = $this->getConfigResolver()->getParameter('content.tree_root.location_id');
        $rootLocation = $locationService->loadLocation($rootLocationId);
        //$currentLocationId = 2;

        return $this->render(
            'themes/standard/list/rides.html.twig',
            [
                'pagerRides' => $this->findRides($rootLocation, $request),
            ]
        );
    }

    private function findRides(Location $rootLocation, Request $request)
    {
        $query = new Query();
        $query->query = new Criterion\LogicalAnd(
            [
                new Criterion\Subtree($rootLocation->pathString),
                new Criterion\Visibility(Criterion\Visibility::VISIBLE),
                new Criterion\ContentTypeIdentifier(['ride']),
            ]
        );
        $query->sortClauses = [
            new SortClause\DatePublished(Query::SORT_ASC),
        ];

        $pager = new Pagerfanta(
            new ContentSearchAdapter($query, $this->getRepository()->getSearchService())
        );
        $pager->setMaxPerPage(10);
        $pager->setCurrentPage($request->get('page', 1));

        return $pager;
    }
}
