<?php
/**
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package manul.core
 */

/**
 * Simple class loader.
 *
 * @author Alexey Shockov <alexey@shockov.com>
 *
 * @package manul.core
 */
class mnlClassLoader
{
    private $_aFileMap = array(
        'mnlCollectorPacketBuilder'                   => '/mnlCollectorPacketBuilder.php',
        'mnlCollectorController'                      => '/mnlCollectorController.php',
        'mnlComponentFactory'                         => '/mnlComponentFactory.php',
        'mnlDefaultSynchronizationComponent'          => '/mnlDefaultSynchronizationComponent.php',
        'mnlComponent'                                => '/mnlComponent.php',
        'mnlCollector'                                => '/mnlCollector.php',
        'mnlImporterPacketProcessor'                  => '/mnlImporterPacketProcessor.php',
        'mnlPacketProcessor'                          => '/mnlPacketProcessor.php',
        'mnlImporterController'                       => '/mnlImporterController.php',
        'mnlMessageProcessor'                         => '/mnlMessageProcessor.php',
        'mnlEntityImporter'                           => '/mnlEntityImporter.php',
        'mnlException'                                => '/mnlException.php',
        'mnlEntityCollector'                          => '/mnlEntityCollector.php',
        'mnlConnectableEntityImporter'                => '/mnlConnectableEntityImporter.php',
        'mnlDeletionCollector'                        => '/mnlDeletionCollector.php',
        'mnlEntityDeletionCollector'                  => '/mnlEntityDeletionCollector.php',
        'mnlEntityDeletionImporter'                   => '/mnlEntityDeletionImporter.php',
        'mnlEntityCollectorController'                => '/mnlEntityCollectorController.php',
        'mnlUnresolvedDependencyException'            => '/mnlUnresolvedDependencyException.php',
        'mnlAssignPacketProcessor'                    => '/mnlAssignPacketProcessor.php',
        'mnlEnvironmentException'                     => '/mnlEnvironmentException.php',
        'mnlRegistry'                                 => '/mnlRegistry.php',

        'mnlLogFormatter'                             => '/logging/mnlLogFormatter.php',

        'mnlCollectorUtf8EncodeDecorator'             => '/utf8/mnlCollectorUtf8EncodeDecorator.php',
        'mnlEntityImporterUtf8DecodeDecorator'        => '/utf8/mnlEntityImporterUtf8DecodeDecorator.php',

        'mnlCollectorPacketBuilderHashDecorator'      => '/hashing/mnlCollectorPacketBuilderHashDecorator.php',
        'mnlHasherType'                               => '/hashing/mnlHasherType.php',
        'mnlImporterPacketProcessorHashDecorator'     => '/hashing/mnlImporterPacketProcessorHashDecorator.php',
        'mnlEntityHasher'                             => '/hashing/mnlEntityHasher.php',
        'mnlHashComparisonException'                  => '/hashing/mnlHashComparisonException.php',

        'mnlResolver'                                 => '/resolving/mnlResolver.php',
        'mnlDbResolver'                               => '/resolving/mnlDbResolver.php',
        'mnlResolverException'                        => '/resolving/mnlResolverException.php',
        'mnlRepeatedBindingException'                 => '/resolving/mnlRepeatedBindingException.php',

        'mnlEntityManifestValidator'                  => '/validation/mnlEntityManifestValidator.php',
        'mnlCollectorPacketBuilderManifestDecorator'  => '/validation/mnlCollectorPacketBuilderManifestDecorator.php',
        'mnlImporterPacketProcessorManifestDecorator' => '/validation/mnlImporterPacketProcessorManifestDecorator.php',
        'mnlEntityDomainValidator'                    => '/validation/mnlEntityDomainValidator.php',
        'mnlManifestValidationException'              => '/validation/mnlManifestValidationException.php',
        'mnlDomainValidationException'                => '/validation/mnlDomainValidationException.php',
        'mnlValidationException'                      => '/validation/mnlValidationException.php',
        'mnlEntityRuleValidator'                      => '/validation/mnlEntityRuleValidator.php',

        'mnlCollectorPacketBuilderFileDecorator'      => '/filetransfer/mnlCollectorPacketBuilderFileDecorator.php',
        'mnlImporterPacketProcessorFileDecorator'     => '/filetransfer/mnlImporterPacketProcessorFileDecorator.php',

        'mnlLogFormatterDecorator'                    => '/util/mnlLogFormatterDecorator.php',
        'mnlIteratorCallbackDecorator'                => '/util/mnlIteratorCallbackDecorator.php',
        'mnlZendDbMySqlUnbufferedQueryAdapter'        => '/util/mnlZendDbMySqlUnbufferedQueryAdapter.php',
        'mnlEntityLockManager'                        => '/util/mnlEntityLockManager.php',
        'mnlResultSetIterator'                        => '/util/mnlResultSetIterator.php',
        'mnlBitrixTypeConverter'                      => '/util/mnlBitrixTypeConverter.php',
        'mnlBitrixDatabaseReconnectDecorator'         => '/util/mnlBitrixDatabaseReconnectDecorator.php',
        'mnlStringHelper'                             => '/util/mnlStringHelper.php',

        'mnlImportingProfiler'                        => '/profiling/mnlImportingProfiler.php',

        'mnlInhibitor'                                => '/inhibitor/mnlInhibitor.php',
        'mnlInhibitorQueueDecorator'                  => '/inhibitor/mnlInhibitorQueueDecorator.php',

        'mnlDefaultFailureHandler'                    => '/failurebroker/mnlDefaultFailureHandler.php',
        'mnlUnresolvedDependencyFailureHandler'       => '/failurebroker/mnlUnresolvedDependencyFailureHandler.php',
        'mnlImporterFailureBroker'                    => '/failurebroker/mnlImporterFailureBroker.php',
    );

    public function __construct() {}

    public function loadClass($sClassName) {
        if (isset($this->_aFileMap[$sClassName])) {
            require_once dirname(__FILE__).$this->_aFileMap[$sClassName];
        }
    }
}
