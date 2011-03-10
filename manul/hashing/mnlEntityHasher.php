<?php
/**
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.hashes
 */

/**
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.hashes
 */
class mnlEntityHasher
{
    public function getHashes($aEntityManifest, $aEntity) {
        $aHashes = array();

        foreach ($aEntityManifest as $sAttributeName => $aAttributeManifest) {
            if ($aAttributeManifest['Hasher']) {
                switch ($aAttributeManifest['Hasher']) {
                    case mnlHasherType::Simple:
                        $aHashes[$sAttributeName] = md5(trim((string)$aEntity[$sAttributeName]));
                        break;
                    case mnlHasherType::Html:
                        $aHashes[$sAttributeName] = md5(strip_tags(trim((string)$aEntity[$sAttributeName])));
                        break;
                }
            }
        }

        return $aHashes;
    }
}
