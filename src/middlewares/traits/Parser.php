<?php

namespace SwFwLess\middlewares\traits;

trait Parser
{
    /**
     * @param $optionNames
     * @return array
     */
    private function parseOptions($optionNames)
    {
        $parsedOptions = [];
        if ($this->getOptions()) {
            $options = explode(',' , $this->getOptions());

            foreach ($optionNames as $i => $optionName) {
                if (isset($options[$i])) {
                    $parsedOptions[$optionName] = $options[$i];
                }
            }
        }

        return $parsedOptions;
    }
}
