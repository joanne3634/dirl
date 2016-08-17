<?php

class BibTeX
{
    private $type;
    private $key = null;
    private $items = array();

    function __construct($raw_bibtex_string)
    {
        $raw_bibtex_string = trim($raw_bibtex_string);
        $bql = strpos($raw_bibtex_string,'{');
        $bgr = strrpos($raw_bibtex_string,'}');
        $this->type = strtoupper(substr($raw_bibtex_string,1,$bql-1));

        $fc = strpos($raw_bibtex_string,',');
        $this->key = substr($raw_bibtex_string,$bql+1,$fc-$bql-1);
        $content = substr($raw_bibtex_string,$fc+1,$bgr-$fc-1);
        //echo $content;

        $pointer = 0;
        $key = null;
        while( true )
        {
            //$last = $pointer;
            if( $key == null )
            {
                $key_end = strpos($content,'=',$pointer);
                if( $key_end === false ) { break; }
                $key = strtolower(trim(substr($content,$pointer,$key_end-$pointer)));
                $pointer = strpos($content,'{',$key_end);
                continue;
            }

            $bqleft = strpos($content,'{',$pointer+1);
            $bqright = strpos($content,'}',$pointer);
            while( $bqleft !== false AND $bqleft < $bqright )
            {
                $bqleft = strpos($content,'{',$bqleft+1);
                $bqright = strpos($content,'}',$bqright+1);
                if( $bqright === false ) { break; }
            }
            if( $bqright === false )
            {
                $this->items[$key] = substr($content,$pointer+1);
                break;
            }
            $this->items[$key] = substr($content,$pointer+1,$bqright-$pointer-1);
            $key = null;
            $pointer = $bqright+2;
        }
    }

    public function Type() { return $this->type; }
    public function KeyName() { return str_replace(':','_',$this->key); }

    public function get($key) { return $this->items[strtolower($key)]; }
    public function has($key)
    {
        $k = strtolower($key);
        if( !isset($this->items[$k]) ) { return false; }
        return !empty($this->items[$k]);
    }

    public function set($key,$value) { $this->items[$key] = $value; }
    public function append($key,$value)
    {
        if( !isset($this->items[$key]) ) { $this->items[$key] = ''; }
        $this->items[$key] .= $value;
    }

    public function toString( $ignore = array(), $padding = 0, $eol = "\r\n" )
    {
        $lines = array();
        $lines[] = '@'.strtoupper($this->type).'{'.$this->key;
        foreach( $this->items as $field => $value )
        {
            if( strlen($value) == 0 ) { continue; }
            $stop = false;
            foreach( $ignore as $ig )
            {
                if( $field == $ig )
                {
                    $stop = true;
                    break;
                }
                if( strpos($ig,'/') !== 0 ) { continue; }
                $stop = preg_match($ig,$field);
                if( $stop ) { break; }
            }
            if( $stop ) { continue; }
            if( $padding > 0 )
            {
                $lines[] = sprintf("  %-{$padding}s = {%s}",strtoupper($field),$value);
            }
            else
            {
                $lines[] = strtoupper($field).'={'.$value.'}';
            }
        }
        return implode(','.$eol,$lines).$eol.'}';
    }

    public function toCitation()
    {
        $citation = '';
        $author_list = explode(' and ',$this->items['author']);
        if( count($author_list) > 2 )
        {
            $last_author = array_pop($author_list);
            $comma = ( count($author_list) > 1 ) ? ',' : '';
            $citation .= implode(', ',$author_list).$comma.' and '.$last_author.', ';
        } else { $citation .= $this->items['author'] . ', '; }

        $tmp_title = $this->items['title'];
        $tmp_title = str_replace('{','',$tmp_title);
        $tmp_title = str_replace('}','',$tmp_title);
        $citation .= '"'.$tmp_title.'," ';

        switch (strtolower($this->type))
        {
        case 'article':
            $citation .= '<i>'.$this->items['journal'].'</i>, ';
            if ($this->has('volume')) { $citation .= 'Vol. '.$this->items['volume'].', '; }
            if ($this->has('number')) { $citation .= 'No. '.$this->items['number'].', '; }
            if ($this->has('vol')) { $citation .= 'Vol. '.$this->items['vol'].', '; }
            if ($this->has('extrapubinfo')) { $citation .= ' '.$this->items['extrapubinfo'].', '; }
            if ($this->has('no')) { $citation .= 'No. '.$this->items['no'].', '; }
            if ( $this->has('page-start') )
            {
                $citation .= 'pp. '.$this->items['page-start'].'--'.$this->items['page-end'].', ';
            }
            if ($this->has('month')) { $citation .= $this->items['month'].', '; }
            $citation .= $this->items['year'].'.';
            break;
        case 'inproceedings':
            $citation .= 'In <i>'.$this->items['booktitle'].'</i>';
            if ($this->has('page-start')) { $citation .= ', pages '.$this->items['page-start'].'-'.$this->items['page-end']; }
            if ($this->has('address'))
            {
                $citation .= ', ';
                $citation .= $this->items['address'].', ';
                if ($this->has('month')) { $citation .= $this->items['month'].' '; }
                $citation .= $this->items['year'].'. ';
                if ($this->has('publisher')) { $citation .= $this->items['publisher'].'.'; }            
            }
            else
            {
                if ($this->has('publisher')) { $citation .= '. '.$this->items['publisher']; }
                $citation .= ', ';
                if ($this->has('month')) { $citation .= $this->items['month'].' '; }
                $citation .= $this->items['year'].'.';
            }
            break;
        case 'media':
            $citation .= '<i>'.$this->items['journal'].'</i>, ';
            if ($this->has('volume')) { $citation .= 'vol. '.$this->items['volume'].', '; }
            if ($this->has('page-start')) { $citation .= 'pp. '.$this->items['page-start'].'-'.$this->items['page-end'].', '; }
            if ($this->has('month')) { $citation .= $this->items['month'].', '; }
            $citation .= $this->items['year'].'.';
            break;
        default:
            break;
        }
        return $citation;
    }
}
