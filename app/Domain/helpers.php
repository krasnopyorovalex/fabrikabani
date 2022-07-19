<?php

if (! function_exists('str_template')) {
    /**
     *
     * @param string $string
     * @param array $params
     * @return mixed
     */
    function str_template(string $string, array $params = [])
    {
        $search = array_map(function ($v) {
            return '{' . $v . '}';
        }, array_keys($params));
        return str_replace($search, array_values($params), $string);
    }
}

if (! function_exists('build_root_child_select')) {
    /**
     * @param $collection
     * @param null $selected
     * @return string
     */
    function build_root_child_select($collection, $selected = null)
    {
        $returnedArray = [];

        foreach ($collection as $item) {
            if (!$item['parent_id']) {
                $returnedArray[] = $item->only(['id', 'name']);
                continue;
            }
            $returnedArray['child_'.$item['parent_id']][] = $item->only(['id', 'name', 'parent_id']);
        }

        return build_options($returnedArray, $selected);
    }
}

if (! function_exists('build_options')) {
    /**
     * @param array $array
     * @param $selected
     * @param string $html
     * @param string $step
     * @param array $helpArray
     * @return string
     */
    function build_options(array $array, $selected, $html = '', $step = '', $helpArray = [])
    {
        $originArray = count($helpArray) ? $helpArray : $array;
        foreach ($array as $item) {
            if (isset($item['id'])) {
                $id = $item['id'];
                $name = $item['name'];

                $html .= '<option value="' . $id . '"' . ($selected == $id ? 'selected=""' : '') . '>' . $step . $name . '</option>' . PHP_EOL;

                if (isset($originArray['child_' . $item['id']])) {
                    $html = build_options($originArray['child_' . $item['id']], $selected, $html, $step . '**', $originArray);
                }
            }
        }
        return $html;
    }
}


if (! function_exists('get_ids_from_array')) {
    /**
     * @param array $array
     * @return array
     */
    function get_ids_from_array(array $array)
    {
        return array_map(function ($item) {
            return $item['id'];
        }, $array);
    }
}


if (! function_exists('is_main_page')) {
    /**
     * @return bool
     */
    function is_main_page()
    {
        return request()->path() === '/';
    }
}

if (! function_exists('add_css_class')) {
    /**
     * @param $item
     * @return string
     */
    function add_css_class($item)
    {
        $classes = [];

        $path = request()->path();

        if (trim($item->link,'/') === $path || $item->link === $path) {
            $classes[] = ' active';
        }
        return count($classes) ? implode(' ', $classes) : '';
    }
}


if (! function_exists('is_shop_pages')) {
    /**
     * @return bool
     */
    function is_shop_pages(): bool
    {
        $path = request()->path();

       return strpos($path, 'catalog') !== false || strpos($path, 'product/') !== false || strpos($path, 'cart') !== false;
    }
}
