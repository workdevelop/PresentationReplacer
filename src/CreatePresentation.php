<?php

namespace PresentationReplacer;

class CreatePresentation implements ICreatePresentation
{
    /**
     * @var string
     */
    private $resultPath;
    /**
     * @var string
     */
    private $regex = '/(\{.*?\})/';
    /**
     * @var string
     */
    private $templatePath;
    private $preparedBefore = false;

    /**
     * @inheritDoc
     */
    public function setTemplatePath(string $path): void
    {
        $this->templatePath = realpath(dirname($path)) . '/' . basename($path);
    }

    /**
     * @inheritDoc
     */
    public function setResultPath(string $path): void
    {
        $this->resultPath = realpath(dirname($path)) . '/' . basename($path);
    }

    /**
     * @inheritDoc
     */
    public function setVariableRegex(string $regex): void
    {
        $this->regex = $regex;
    }

    /**
     * @throws PptException
     */
    private function copyTemplateToResultDestination(): void
    {
        if (!file_exists($this->templatePath)) {
            throw new PptException('Template file not find');
        }
        $resultDir = dirname($this->resultPath);
        if (!is_writable($resultDir)) {
            throw new PptException('path[' . $resultDir . '] is not writable ');
        }
        if (is_file($this->resultPath)) {
            throw new PptException('duplicate of result file [' . $this->resultPath . '] copy filed');
        }
        if (!copy($this->templatePath, $this->resultPath)) {
            throw new PptException('copy file[' . $this->templatePath . '] to [' . $this->resultPath . '] filed');
        }
    }

    /**
     * @throws PptException
     */
    private function prepare(): void
    {
        if (!$this->preparedBefore) {
            $this->copyTemplateToResultDestination();
            $this->preparedBefore = true;
        }
    }

    /**
     * @param callable $callable
     * @throws PptException
     */
    public function iterateFiles(callable $callable)
    {
        $this->prepare();

        $zip = new \ZipArchive();
        if (!file_exists($this->resultPath)) {
            throw new PptException('resultPath file not find');
        }
        if (true !== $zip->open($this->resultPath)) {
            throw new PptException('Open filed');
        }
        for ($i = 0; $i < $zip->numFiles; $i++) {
            if (false === $callable($zip, $i)) {
                break;
            }
        }
        $zip->close();
    }

    /**
     * @throws PptException
     */
    public function getAllVariablesAtPresentation(): array
    {
        $regex = $this->regex;
        $data = [];
        $this->iterateFiles(static function (\ZipArchive $zip, int $i) use ($regex, &$data) {
            $content = $zip->getFromIndex($i);
            if (preg_match_all($regex, $content, $matches) && !empty($matches[1])) {
                $data[$zip->getNameIndex($i)] = $matches[1];
            }
        });
        return $data;
    }

    /**
     * @param array $data key - its full variable to replace bu own value
     * @throws PptException
     */
    public function replaceVariables(array $data): void
    {
        $regex = $this->regex;
        $search = array_keys($data);
        $replace = array_values($data);
        $this->iterateFiles(
            static function (\ZipArchive $zip, int $i) use ($regex, $search, $replace) {
                $content = $zip->getFromIndex($i);
                if (preg_match_all($regex, $content, $matches) && !empty($matches[1])) {
                    $content = str_replace($search, $replace, $content);
                    $localName = $zip->getNameIndex($i);
                    $zip->addFromString($localName, $content);
                }
            }
        );
    }

    public function download(string $fileName): void
    {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($this->resultPath));
        flush(); // Flush system output buffer
        readfile($this->resultPath);
        die();
    }

    /**
     * @param string $relativePath such as ppt/charts/chart1.xml'
     * @return string full file content
     * @throws PptException
     */
    public function getFileContentByRelativePath(string $relativePath): string
    {
        $this->iterateFiles(
            static function (\ZipArchive $zip, int $i) use ($relativePath, &$content) {
                unset($i);
                $content = $zip->getFromName($relativePath);
                return false;
            }
        );
        return $content ?? '';
    }

    /**
     * @param string $relativePath such as ppt/charts/chart1.xml
     * @param string $content full file content
     * @throws PptException
     */
    public function setFileContentByRelativePath(string $relativePath, string $content): void
    {
        $this->iterateFiles(
            static function (\ZipArchive $zip, int $i) use ($relativePath, &$content) {
                unset($i);
                $zip->addFromString($relativePath, $content);
                return false;
            }
        );
    }

    /**
     * @param string $relativePath
     * @param string $newFilePathAbsolutePath
     * @throws PptException
     */
    public function replaceFile(string $relativePath, string $newFilePathAbsolutePath)
    {
        $this->iterateFiles(
            static function (\ZipArchive $zip, int $i) use ($relativePath, $newFilePathAbsolutePath) {
                unset($i);
                $zip->addFromString($relativePath, file_get_contents($newFilePathAbsolutePath));
                return false;
            }
        );
    }
}
