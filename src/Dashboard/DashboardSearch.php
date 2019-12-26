<?php

/**
 * Created by PhpStorm.
 * User: Lucas
 * Date: 23/12/2019
 * Time: 09:00
 * Project: Monofony.
 */

namespace App\Dashboard;

use Pagerfanta\Pagerfanta;
use Sylius\Bundle\GridBundle\Doctrine\ORM\DataSource;
use Sylius\Bundle\GridBundle\Doctrine\ORM\Driver;
use Sylius\Bundle\GridBundle\Renderer\TwigGridRenderer;
use Sylius\Component\Grid\Definition\Filter;
use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Filtering\FiltersApplicator;
use Sylius\Component\Grid\Filtering\FiltersApplicatorInterface;
use Sylius\Component\Grid\Parameters;
use Sylius\Component\Grid\Provider\GridProviderInterface;
use Sylius\Component\Grid\View\GridViewFactory;
use Sylius\Component\Grid\View\GridViewFactoryInterface;
use Symfony\Component\Templating\EngineInterface;

class DashboardSearch
{
    /** @var array */
    private $gridDefinitions;

    /** @var GridProviderInterface */
    private $gridProvider;
    /**
     * @var FiltersApplicator
     */
    private $applicator;
    /**
     * @var DataSource
     */
    private $dataSource;
    /**
     * @var Driver
     */
    private $driver;
    /**
     * @var EngineInterface
     */
    private $engine;
    /**
     * @var GridViewFactory
     */
    private $gridViewFactory;
    /**
     * @var TwigGridRenderer
     */
    private $gridRenderer;

    public function __construct(
        iterable $gridDefinitions,
        GridProviderInterface $gridProvider,
        FiltersApplicatorInterface $applicator,
        Driver $driver,
        TwigGridRenderer $gridRenderer,
        GridViewFactoryInterface $gridViewFactory
    ) {
        $this->gridDefinitions = $gridDefinitions;
        $this->gridProvider = $gridProvider;
        $this->applicator = $applicator;
        $this->driver = $driver;
        $this->gridViewFactory = $gridViewFactory;
        $this->gridRenderer = $gridRenderer;
    }

    public function search(string $searchQuery)
    {
        /** @var Grid[] $grids */
        $grids = $this->getGrids();

        /** @var Grid $grid */
        foreach ($grids as $grid) {
            $this->addSearchFilter($grid);

            $filterParameters = new Parameters(['criteria' => [
                'admin_global_search' => $searchQuery,
            ]]);

            $dataSource = $this->driver->getDataSource($grid->getDriverConfiguration(), $filterParameters);

            $this->applicator->apply($dataSource, $grid, $filterParameters);

            /** @var Pagerfanta $results */
            $results = $dataSource->getData($filterParameters);

            $this->gridRenderer->render($this->gridViewFactory->create($grid, $filterParameters), 'backend/search/index.html.twig');
        }

        die;
    }

    private function getGrids(): array
    {
        /** @var Grid[] $grids */
        $grids = [];
        foreach ($this->gridDefinitions as $gridKey => $gridDefinition) {
            $grids[] = $this->gridProvider->get($gridKey);
        }

        return $grids;
    }

    private function addSearchFilter(Grid $grid): Grid
    {
        $searchFilter = Filter::fromNameAndType('admin_global_search', 'string');

        $fields = [];

        /** @var Filter $filter */
        foreach ($grid->getFilters() as $filter) {
            $filterOptions = $filter->getOptions();

            if (!empty($filterOptions['fields'])) {
                $fields = array_merge($filterOptions['fields'], $fields);
            }
        }

        $searchFilter->setOptions(['fields' => array_unique($fields)]);
        $grid->addFilter($searchFilter);

        return $grid;
    }
}
