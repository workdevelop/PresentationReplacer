<?php

namespace PresentationReplacer;

interface ICreatePresentation
{
    /**
     * Full path to result file
     * @param string $path
     */
    public function setResultPath(string $path): void;

    /**
     * Regular expression for pre-defined variable at presentation
     * @param string $regex
     */
    public function setVariableRegex(string $regex): void;

    /**
     * Full path to template file
     * @param string $path
     */
    public function setTemplatePath(string $path): void;

    /**
     * All variants pre-defined variables at presentation which similar for regexp, which set to setResultPath method
     * @return array
     */
    public function getAllVariablesAtPresentation(): array;

    /**
     * replace pre-defined variables($data array key) at presentation by own values($data array values)
     * @param array $data
     */
    public function replaceVariables(array $data): void;

    /**
     * Method to download presentation for browser
     * @param string $fileName
     */
    public function download(string $fileName) :void;
}