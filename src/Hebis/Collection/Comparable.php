<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 05.02.16
 * Time: 13:48
 */

namespace Hebis\Collection;


    /**
     * Comparable Interface for Elements as part of an <code>Model\ArrayList</code> (e.g. Post, Tag, Group) that should be
     * comparable and sortable.
     *
     * @since 24.06.15
     * @author Sebastian Böttger / boettger@cs.uni-kassel.de
     */
interface Comparable {

    public function compare(Comparable $b);
}