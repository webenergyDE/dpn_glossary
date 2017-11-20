<?php
namespace Featdd\DpnGlossary\Tests\ViewHelpers\Widget\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2017 Daniel Dorndorf <dorndorf@featdd.de>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use Featdd\DpnGlossary\ViewHelpers\Widget\Controller\PaginateController;
use TYPO3\CMS\Core\Tests\UnitTestCase;

/**
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PaginateContollerTest extends UnitTestCase
{

    /**
     * @var \Featdd\DpnGlossary\ViewHelpers\Widget\Controller\PaginateController|\TYPO3\CMS\Core\Tests\AccessibleObjectInterface
     */
    protected $controller;

    /**
     * @var string
     */
    protected $characters = 'A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z';

    public function setUp()
    {
        $this->controller = $this->getAccessibleMock(PaginateController::class);
    }

    public function tearDown()
    {
        unset($this->controller);
    }

    /**
     * @test
     */
    public function paginateArgumentsTest()
    {
        $this->assertEquals(
            [
                '@widget_0' => [
                    'character' => 'T',
                ],
            ],
            PaginateController::paginationArguments('Test', $this->characters)
        );

        $this->assertEquals(
            [
                '@widget_0' => [
                    'character' => 'A',
                ],
            ],
            PaginateController::paginationArguments('Ätest', $this->characters)
        );

        //Use characters plus umlauts
        $characters = $this->characters . ',Ä,Ö,Ü';

        $this->assertEquals(
            [
                '@widget_0' => [
                    'character' => 'AE',
                ],
            ],
            PaginateController::paginationArguments('Ätest', $characters)
        );

        $this->assertEquals(
            [
                '@widget_0' => [
                    'character' => 'OE',
                ],
            ],
            PaginateController::paginationArguments('Ötest', $characters)
        );

        $this->assertEquals(
            [
                '@widget_0' => [
                    'character' => 'UE',
                ],
            ],
            PaginateController::paginationArguments('Ütest', $characters)
        );
    }
}
