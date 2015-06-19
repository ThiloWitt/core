<?php

namespace Zikula\Bundle\CoreBundle\Bundle;

use Symfony\Component\HttpKernel\Exception\PreconditionRequiredHttpException;
use Zikula\Common\Translator\TranslatorTrait;

class MetaData
{
    use TranslatorTrait;

    private $name;
    private $version;
    private $description;
    private $type;
    private $dependencies;
    private $shortName;
    private $class;
    private $namespace;
    private $basePath;
    private $rootPath;
    private $autoload;
    private $displayName;
    private $url;
    private $oldNames;
    private $capabilities;
    private $securitySchema;
    private $extensionType;
    private $directory;

    public function __construct($json)
    {
        $this->name = $json['name'];
        $this->version = isset($json['version']) ? $json['version'] : '';
        $this->description = isset($json['description']) ? $json['description'] : '';
        $this->type = $json['type'];
        $this->dependencies = $this->formatDependencies($json);
        $this->shortName = $json['extra']['zikula']['short-name'];
        $this->class = $json['extra']['zikula']['class'];
        $this->namespace = substr($this->class, 0, strrpos($this->class, '\\')+1);
        $this->basePath = $json['extra']['zikula']['base-path'];
        $this->rootPath = $json['extra']['zikula']['root-path'];
        $this->autoload = $json['autoload'];
        $this->displayName = isset($json['extra']['zikula']['displayname']) ? $json['extra']['zikula']['displayname'] : '';
        $this->url = isset($json['extra']['zikula']['url']) ? $json['extra']['zikula']['url'] : '';
        $this->oldNames = isset($json['extra']['zikula']['oldnames']) ? $json['extra']['zikula']['oldnames'] : array();
        $this->capabilities = isset($json['extra']['zikula']['capabilities']) ? $json['extra']['zikula']['capabilities'] : array();
        $this->securitySchema = isset($json['extra']['zikula']['securityschema']) ? $json['extra']['zikula']['securityschema'] : array();
        $this->extensionType = $json['extensionType'];
        $this->directory = $json['name'];
    }

    public function getName()
    {
        return $this->name;
    }

    public function getVersion()
    {
        return$this->version;
    }

    public function getShortName()
    {
        return $this->shortName;
    }

    public function getPsr0()
    {
        return isset($this->autoload['psr-0']) ? $this->autoload['psr-0'] : array();
    }

    public function getPsr4()
    {
        return isset($this->autoload['psr-4']) ? $this->autoload['psr-4'] : array();
    }

    public function getAutoload()
    {
        return $this->autoload;
    }

    public function getBasePath()
    {
        return $this->basePath;
    }

    public function getRootPath()
    {
        return $this->rootPath;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getDescription()
    {
        $this->confirmTranslator();
        return $this->__($this->description);
    }

    public function getDependencies()
    {
        return $this->dependencies;
    }

    public function getDisplayName()
    {
        $this->confirmTranslator();
        return $this->__($this->displayName);
    }

    public function getUrl()
    {
        $this->confirmTranslator();
        return $this->__($this->url);
    }

    public function getOldNames()
    {
        return $this->oldNames;
    }

    public function getCapabilities()
    {
        return $this->capabilities;
    }

    public function getSecuritySchema()
    {
        return $this->securitySchema;
    }

    public function getExtensionType()
    {
        return $this->extensionType;
    }

    private function formatDependencies(array $json)
    {
        $dependencies = array();
        if (!empty($json['require'])) {
            foreach ($json['require'] as $package => $version) {
                $dependencies[] = [
                    'modname' => $package,
                    'minversion' => $version,
                    'maxversion' => $version,
                    'status' => \ModUtil::DEPENDENCY_REQUIRED
                ];
            }
        } else {
            $dependencies[] = [
                'modname' => 'zikula/core',
                'minversion' => '>=1.4.1 <3.0.0',
                'maxversion' => '>=1.4.1 <3.0.0',
                'status' => \ModUtil::DEPENDENCY_REQUIRED
            ];
        }
        if (!empty($json['suggest'])) {
            foreach ($json['suggest'] as $package => $version) {
                $dependencies[] = [
                    'modname' => $package,
                    'minversion' => '-1',
                    'maxversion' => '100',
                    'reason' => $version,
                    'status' => \ModUtil::DEPENDENCY_RECOMMENDED
                ];
            }
        }
        return $dependencies;
    }

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    private function confirmTranslator()
    {
        if (!isset($this->translator)) {
            throw new PreconditionRequiredHttpException(sprintf("The translator property is not set correctly in %s", __CLASS__));
        }
    }

    public function setDirectoryFromBundle(\Zikula\Core\AbstractBundle $bundle)
    {
        $parts = explode('/', $bundle->getRelativePath());
        array_shift($parts);
        $this->directory = implode('/', $parts);
    }

    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * @return array
     */
    public function getFilteredVersionInfoArray()
    {
        return array(
            'name' => $this->getShortName(),
            'type' => $this->getExtensionType(),
            'directory' => $this->getDirectory(),
            'displayname' => $this->getDisplayName(),
            'oldnames' => $this->getOldNames(),
            'description' => $this->getDescription(),
            'version' => $this->getVersion(),
            'url' => $this->getUrl(),
            'capabilities' => $this->getCapabilities(),
            'securityschema' => $this->getSecuritySchema(),
            'dependencies' => $this->getDependencies(),
            'core_min' => '1.4.0', // will be 1.4.1 but is @deprecated - will use vierbergenlars/php-semver instead
            'core_max' => '2.9.99' // @deprecated
        );
    }
}
