<?php

/*
 * This file is part of monofony.
 *
 * (c) Mobizel
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Controller;

use App\Dashboard\DashboardSearch;
use App\Dashboard\DashboardStatisticsProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;
use Webmozart\Assert\Assert;

final class DashboardController
{
    /** @var DashboardStatisticsProvider */
    private $statisticsProvider;

    /** @var EngineInterface */
    private $templating;

    /** @var DashboardSearch */
    private $dashboardSearch;

    public function __construct(DashboardStatisticsProvider $statisticsProvider, EngineInterface $templating, DashboardSearch $dashboardSearch)
    {
        $this->statisticsProvider = $statisticsProvider;
        $this->templating = $templating;
        $this->dashboardSearch = $dashboardSearch;
    }

    public function indexAction(): Response
    {
        $statistics = $this->statisticsProvider->getStatistics();
        $content = $this->templating->render('backend/index.html.twig', ['statistics' => $statistics]);

        return new Response($content);
    }

    public function searchAction(Request $request): Response
    {
        $searchQuery = $request->get('q');
        Assert::notEmpty($searchQuery);

        $searchResults = $this->dashboardSearch->search($searchQuery);
        $content = $this->templating->render('', ['searchResults' => $searchResults]);

        return new Response($content);
    }
}
