<?php

class myXmlParser extends XML_Parser
{

  function myXmlParser()
  {
    parent::XML_Parser();
  }

 /**
  * handle start element
  *
  * @access private
  * @param  resource  xml parser resource
  * @param  string    name of the element
  * @param  array     attributes
  */
  function startHandler($xp, $name, $attribs)
  {
    printf($name);
  }

 /**
  * handle end element
  *
  * @access private
  * @param  resource  xml parser resource
  * @param  string    name of the element
  */
  function endHandler($xp, $name)
  {
    printf($name);
  }

 /**
  * handle character data
  *
  * @access private
  * @param  resource  xml parser resource
  * @param  string    character data
  */
  function cdataHandler($xp, $cdata)
  {
    printf($cdata);
  }
}

?>
