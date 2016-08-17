<?php
namespace kjPHP
{
    function toString($arr)
    {
        $item = array();
        if( $arr != null )
        {
            foreach( $arr as $key => $val )
            {
                $item[] = "'{$key}' => '{$val}'";
            }
        }
        return 'array('.implode(',',$item).')';
    }

    function toJSON($val,$ignore_empty = false)
    {
        switch( gettype($val) )
        {
        case 'boolean':
            $data = ($val) ? 'true' : 'false';
            break;
        case 'integer':
        case 'double':
            $data = $val;
            break;
        case 'string':
            $lines = explode("\r\n",$val);
            $data = ( count($lines) > 1 ) ? toJSON($lines) : '"'.htmlspecialchars( trim($val), ENT_QUOTES ).'"';
            break;
        case 'array':
            $results = array();
            $start = '[';
            $end = ']';
            foreach( $val as $key => $it )
            {
                if( $ignore_empty AND empty($it) ) { continue; }
                $prefix = '';
                switch( gettype($key) )
                {
                case 'string':
                    $start = '{';
                    $end = '}';
                    $prefix = '"'.$key.'":';
                    break;
                default:
                    break;
                }
                $results[] = $prefix.toJSON($it);
            }
            $data = $start.implode(',',$results).$end;
            break;
        case 'NULL':
            $data = 'null';
            break;
        default:
            $data = '{}';
            break;
        }
        return $data;
    }

    function drawHTMLElement($tag,$attr,$content=null)
    {
        echo '<'.$tag;
        foreach( $attr as $k => $v ) { echo ' '.$k.'="'.$v.'"'; }
        echo empty($content) ? ' />' : '>'.$content.'</'.$tag.'>';
    }

    function toHTML($str,$decode=true,$br=PHP_EOL)
    {
        $html = $str;
        if( count($str) > 1 AND $br != null ) { $html = implode($br,$str); }
        if( $decode ) { $html = htmlspecialchars_decode($html,ENT_QUOTES); }
        return $html;
    }

    class PageBar
    {
        private $near = 5;
        private $prev = '<';
        private $next = '>';
        private $container = array(
            'tag' => 'div',
            'classname' => 'page-bar'
        );
        private $button = array(
            'tag' => 'a',
            'type' => 'href',
            'pattern' => '%id%.html',
            'classname' => 'page-button'
        );
        private $current = array(
            'tag' => 'span',
            'classname' => 'page-button'
        );

        private function copy($keys,$src,&$des)
        {
            foreach( $keys as $key )
            {
                if( !isset($src[$key]) ) { continue; }
                $des[$key] = $src[$key];
            }
        }

        function __construct($neighbor = 5,$style = null)
        {
            $this->near = $neighbor;
            if( !empty($style) )
            {
                if( isset($style['prev']) ) { $this->prev = $style['prev']; }
                if( isset($style['next']) ) { $this->next = $style['next']; }

                if( isset($style['container']) ) { $this->copy(array('tag','classname'),$style['container'],$this->container); }
                if( isset($style['button']) ) { $this->copy(array('tag','type','pattern','classname'),$style['button'],$this->button); }
                if( isset($style['current']) ) { $this->copy(array('tag','classname'),$style['current'],$this->current); }
            }
        }

        private function createElement($setting,$content = '')
        {
            $element = '<' . $setting['tag'];
            if( isset($setting['classname']) ) { $element .=  ' class="' . $setting['classname'] . '">'; }
            if( !empty($content) ) { $element .= $content; }
            $element .= '</' . $setting['tag'] . '>';
            return $element;
        }

        private function createButton($setting,$page,$content = '')
        {
            $element = '<' . $setting['tag'];
            if( isset($setting['classname']) ) { $element .=  ' class="' . $setting['classname'] . '"'; }
            $element .= ' ' . $setting['type'] . '="' . str_replace('%id%',$page,$setting['pattern']) . '">';
            if( !empty($content) ) { $element .= $content; }
            $element .= '</' . $setting['tag'] . '>';
            return $element;
        }

        public function draw($total,$now,$container = true)
        {
            if( $total <= 1 ) { return ''; }
            $result = array();
            if( $now > 1 )
            {
                if( $now > $this->near ) { $result[] = $this->createButton($this->button,1,1); }
                $result[] = $this->createButton($this->button,$now-1,$this->prev);
            }
            for($i=1;$i<=$total;$i++)
            {
                $delta = abs( $i - $now );
                if( $delta >= $this->near ) { continue; }
                $result[] = ( $i == $now ) ? $this->createElement($this->current,$now) : $this->createButton($this->button,$i,$i);
            }
            if( $now < $total )
            {
                $result[] = $this->createButton($this->button,$now+1,$this->next);
                if( $now < ($total-$this->near) ) { $result[] = $this->createButton($this->button,$total,$total); }
            }

            $page_bar = implode($result);
            return ( $container ) ? $this->createElement($this->container,$page_bar) : $page_bar;
        }
    }
}