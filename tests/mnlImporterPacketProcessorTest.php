<?php
/**
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.tests.core
 */

require_once 'PHPUnit/Framework/TestCase.php';

/**
 * @author Alexey Shockov <alexey@shockov.com>
 * @author Maxim Pshenichnikov <m.pshenichnikov@gmail.com>
 *
 * @package manul.tests.core
 */
class mnlImporterPacketProcessorTest
    extends PHPUnit_Framework_TestCase
{
    protected function setUp() {
        mnlRegistry::set(
            'logger',
            new Zend_Log(new Zend_Log_Writer_Null())
        );

        mnlRegistry::set(
            'importing_profiler',
            $this->getMock('mnlImportingProfiler', array(), array(), '', false)
        );
    }

    /**
     * При обработке всегда производится проверка дат, и, если проверка не прошла, процесс
     * просто прекращается (рапортуя в журнал, естественно).
     */
    public function testOlderPacketProcessing() {
        $aRemoteId       = 1;
        $iLocalId        = 2;
        $sEntityType     = 'TestType';
        $aEntity         = array(
            'Id'           => $aRemoteId,
            'ModifiedDate' => time(),
            'Name'         => 'Test Entity',
        );
        $aPacket         = array(
            'EntityType'   => $sEntityType,
            'Entity'       => $aEntity,
        );
        $aEntityManifest = array(
            'Id'           => array(),
            'ModifiedDate' => array(),
            'Name'         => array(),
        );

        $oResolver = $this->getMock('mnlResolver');
        $oResolver
            ->expects($this->once())
            ->method('resolveRemoteId')
            ->with($sEntityType, $aRemoteId)
            ->will(
                $this->returnValue($iLocalId)
            );

        $oEntityImporter = $this->getMock('mnlEntityImporter');
        $oEntityImporter
            ->expects($this->atLeastOnce())
            ->method('getEntityLastModifiedDate');

        $oComponent = $this->getMock('mnlComponent');
        $oComponent
            ->expects($this->once())
            ->method('getManifest')
            ->with()
            ->will(
                $this->returnValue($aEntityManifest)
            );
        $oComponent
            ->expects($this->once())
            ->method('getEntityType')
            ->with()
            ->will(
                $this->returnValue($sEntityType)
            );
        $oComponent
            ->expects($this->once())
            ->method('getEntityImporter')
            ->with($iLocalId)
            ->will(
                $this->returnValue($oEntityImporter)
            );

        $oLockManager = $this->getMock('mnlEntityLockManager', array(), array(), '', false);

        $oPacketProcessor = new mnlImporterPacketProcessor(
            $oResolver,
            $cNeedImportStrategy = create_function('', 'return false;'),
            $oLockManager
        );

        $this->assertNull(
            $oPacketProcessor->processPacket($oComponent, $aPacket)
        );
    }

    /**
     * Обработка нормального, нового по версии содержимого пакета.
     */
    public function testPacketProcessing() {
        $aRemoteId       = 1;
        $iLocalId        = 2;
        $sEntityType     = 'TestType';
        $aEntity         = array(
            'Id'           => $aRemoteId,
            'ModifiedDate' => time(),
            'Name'         => 'Test Entity',
        );
        $aPacket         = array(
            'EntityType'   => $sEntityType,
            'Entity'       => $aEntity,
        );
        $aEntityManifest = array(
            'Id'           => array(),
            'ModifiedDate' => array(),
            'Name'         => array(),
        );

        $oResolver = $this->getMock('mnlResolver');
        $oResolver
            ->expects($this->once())
            ->method('resolveRemoteId')
            ->with($sEntityType, $aRemoteId)
            ->will(
                $this->returnValue($iLocalId)
            );

        $oDomainValidator = $this->getMock('mnlEntityDomainValidator');
        $oDomainValidator
            ->expects($this->once())
            ->method('isValid')
            ->with()
            ->will(
                $this->returnValue(true)
            );

        $oEntityImporter = $this->getMock('mnlEntityImporter');
        
        // Запись должна подавать на импорт обязательно без идентификатора в сторонней системе.
        $aImportingEntity = $aEntity;
        unset($aImportingEntity['Id']);
        
        $oEntityImporter
            ->expects($this->once())
            ->method('fillEntity')
            ->with($aImportingEntity);
        $oEntityImporter
            ->expects($this->once())
            ->method('getEntityDomainValidator')
            ->with()
            ->will(
                $this->returnValue($oDomainValidator)
            );
        $oEntityImporter
            ->expects($this->once())
            ->method('saveEntity')
            ->with()
            ->will($this->returnValue($iLocalId));
        $oEntityImporter
            ->expects($this->once())
            ->method('getEntityLastModifiedDate');

        $oComponent = $this->getMock('mnlComponent');
        $oComponent
            ->expects($this->once())
            ->method('getManifest')
            ->with()
            ->will(
                $this->returnValue($aEntityManifest)
            );
        $oComponent
            ->expects($this->once())
            ->method('getEntityType')
            ->with()
            ->will(
                $this->returnValue($sEntityType)
            );
        $oComponent
            ->expects($this->once())
            ->method('getEntityImporter')
            ->with($iLocalId)
            ->will(
                $this->returnValue($oEntityImporter)
            );

        $oLockManager = $this->getMock('mnlEntityLockManager', array(), array(), '', false);

        $oPacketProcessor = new mnlImporterPacketProcessor(
            $oResolver,
            $cNeedImportStrategy = create_function('', 'return true;'),
            $oLockManager
        );

        $this->assertEquals(
            $iLocalId,
            $oPacketProcessor->processPacket($oComponent, $aPacket)
        );
    }
}
