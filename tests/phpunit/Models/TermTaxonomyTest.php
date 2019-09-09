<?php

namespace UnderScorer\ORM\Tests\Models;

use UnderScorer\ORM\Models\TermTaxonomy;
use UnderScorer\ORM\Tests\TestCase;

/**
 * Class TermTaxonomyTest
 * @package UnderScorer\ORM\Tests\Models
 */
final class TermTaxonomyTest extends TestCase
{

    /**
     * @return void
     */
    public function testParentTaxonomy(): void
    {
        $parentID = $this->factory()->term->create();
        $thisID   = $this->factory()->term->create();

        /** @var TermTaxonomy $thisTerm */
        $thisTerm = TermTaxonomy::query()->find( $thisID );
        /** @var TermTaxonomy $parentTerm */
        $parentTerm = TermTaxonomy::query()->find( $parentID );

        $thisTerm->parent = $parentTerm->term_taxonomy_id;

        $thisTerm->save();
        $thisTerm->refresh();

        $thisTermParent = $thisTerm->parentTaxonomy;

        $this->assertEquals( $parentTerm->term_taxonomy_id, $thisTermParent->term_taxonomy_id );
    }

}
