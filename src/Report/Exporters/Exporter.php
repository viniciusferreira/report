<?php
namespace Fireguard\Report\Exporters;

use Fireguard\Report\Contracts\ExporterContract;
use Fireguard\Report\Contracts\ReportContract;

abstract class Exporter implements ExporterContract
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var string
     */
    protected $fileName;

    /**
     * @var string
     */
    protected $extension;

    /**
     * Path for save file
     *
     * @var string
     */
    protected $path;

    /**
     * Time for expire process
     *
     * @var int
     */
    protected $timeout = 10;

    /**
     * Config Valid Options file to Exporter Format
     *
     * @var array
     */
    protected $configValidOptions = [];

    /**
     * Config Default Options file to Exporter Format
     *
     * @var array
     */
    protected $configDefaultOptions = [];

    /**
     * ExporterContract constructor.
     * @param string $path
     * @param string $fileName
     * @param array $config
     * @return Exporter
     */
    public function __construct($path = '', $fileName = '', $config = [])
    {
        $this->setPath($path);
        $this->setFileName($fileName);
        $this->config = $config;
        $this->initialize();
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @param unix_permission $mode Permission
     * @return Exporter
     */
    public function setPath($path, $mode = 0777)
    {
        $tmpPath = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        if ( empty($path)
            || ( !file_exists($path) && !mkdir($path, $mode, true) )
            || ( !empty($this->fileName) && !(touch($tmpPath.$this->getFileName(), $mode)))
        ) {
            $this->path = sys_get_temp_dir().DIRECTORY_SEPARATOR;
            return $this;
        }

        $this->path = $tmpPath;
        return $this;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param $fileName
     * @return Exporter
     */
    public function setFileName($fileName)
    {
        $tmpFileName = $fileName.$this->extension;
        if ( empty($fileName)
            || (file_exists($this->getPath().$tmpFileName) && !is_writable($this->getPath().$tmpFileName) )
        ) {
            $this->fileName = 'report-'.sha1(time());
            return $this;
        }
        $this->fileName = $fileName;
        return $this;
    }

    /**
     * @return string
     */
    public function getFullPath()
    {
        return $this->getPath().$this->getFileName().$this->extension;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     * @return Exporter
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * @return array
     */
    public function getConfigDefaultOptions()
    {
        return $this->configDefaultOptions;
    }

    /**
     * @param array $options
     * @return Exporter
     */
    public function setConfigDefaultOptions($options)
    {
        $this->configDefaultOptions = $options;
        return $this;
    }

    /**
     * @return array
     */
    public function getConfigValidOptions()
    {
        return $this->configValidOptions;
    }

    /**
     * @param array $options
     * @return Exporter
     */
    public function setConfigValidOptions($options)
    {
        $this->configValidOptions = $options;
        return $this;
    }

    public function compress($buffer)
    {
        // remove comments
        $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
        // remove tabs, spaces, newlines, etc.
        $buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '   ', '    '), '', $buffer);
        return $buffer;
    }

    /**
     * @return void
     */
    abstract function initialize();

    /**
     * @param ReportContract $report
     * @return string
     */
    abstract function generate(ReportContract $report);
}