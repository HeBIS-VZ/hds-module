<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 27.01.17
 * Time: 09:21
 */

namespace Hebis\Csl\Model\Layout;

/**
 * Interface CslName
 * Western names frequently contain one or more name particles (e.g. “de” in the Dutch name “W. de Koning”). These name
 * particles can be either kept or dropped when only the surname is shown: these two types are referred to as
 * non-dropping and dropping particles, respectively. A single name can contain particles of both types (with
 * non-dropping particles always following dropping particles). For example, “W. de Koning” and the French name
 * “Jean de La Fontaine” can be deconstructed into:
 *
 * <pre>
 * {
 *  "author": [
 *      {
 *          "given": "W.",
 *          "non-dropping-particle": "de",
 *          "family": "Koning"
 *      },
 *      {
 *          "given": "Jean",
 *          "dropping-particle": "de",
 *          "non-dropping-particle": "La",
 *          "family": "Fontaine"
 *      }
 *  ]
 * }
 * </pre>
 *
 * When just the surname is shown, only the non-dropping-particle is kept: “De Koning” and “La Fontaine”.
 *
 * @package Hebis\Csl
 */
interface CslName
{
    /**
     * Given name
     * @return string
     */
    public function getGiven();

    /**
     * e.g. the “de“ in “Jean de La Fontaine“
     * @return string
     */
    public function getDroppingParticle();

    /**
     * e.g. “La“ in “Jean de La Fontaine“
     * @return string
     */
    public function getNonDroppingParticle();

    /**
     * Family name
     * @return string
     */
    public function getFamily();

    /**
     * e.g. “II“ in “Elisabeth II“ or “XVI“ in “Benedict XVI“
     * @return string
     */
    public function getSuffix();

}
