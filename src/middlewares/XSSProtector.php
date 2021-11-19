<?php

namespace SwFwLess\middlewares;

use SwFwLess\components\http\Request;
use SwFwLess\components\utils\html\Purifier;

class XSSProtector extends AbstractMiddleware
{
    public function handle(Request $request)
    {
        $getParams = $request->get();
        $request->setAllGet($this->filter($getParams));

        $postParams = $request->post();
        $request->setAllPost($this->filter($postParams));

        return $this->next();
    }

    protected function filter($value)
    {
        if (is_array($value)) {
            return array_map([static::class, __METHOD__], $value);
        } else {
            if (Purifier::supportPurify()) {
                return Purifier::purify($value);
            } else {
                return htmlspecialchars($value);
            }
        }
    }
}
