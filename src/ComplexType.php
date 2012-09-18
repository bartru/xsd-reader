<?php
namespace goetas\xml\xsd;
use DOMElement;
class ComplexType extends AbstractComplexType {
	protected $elements = array ();
	public function getElements() {
		return $this->elements;
	}
	protected $base;
	/**
	 * 
	 * @return \goetas\xml\xsd\SimpleType
	 */
	public function getBase() {
		return $this->base;
	}
	protected function parseElement(DOMElement $node) {
		switch ($node->localName) {
			case "sequence" :
				$this->recurse ( $node );
				break;
			case "complexContent" :
				$this->recurse ( $node );
				break;
			case "extension" :
			case "restriction" :
				
				list ( $ns, $name, $prefix ) = Schema::findParts ( $node, $node->getAttribute ( "base" ) );
				
				$this->base = $this->xsd->findType($ns, $name);
				
				$this->recurse($node);
				
				break;
			
			case "element" :
				if ($node->hasAttribute ( "ref" )) {
					list ( $ns, $name, $prefix ) = Schema::findParts ( $node, $node->getAttribute ( "ref" ) );
					$this->elements [] = $this->xsd->findElement ( $ns, $name );
				} else {
					$min = $node->hasAttribute ( "minOccurs" ) ? $node->getAttribute ( "minOccurs" ) : 1;
					$max = $node->hasAttribute ( "maxOccurs" ) ? $node->getAttribute ( "maxOccurs" ) : 1;

					if ($max == "unbounded") {
						$max = PHP_INT_MAX;
					}
					
					$nillable = $node->getAttribute ( "nillable" ) == "true";
					$qualification = $node->hasAttribute ( "form" )?$node->getAttribute ( "form" ):$this->getSchema()->getElementQualification();
					
					list ( $ns, $name, $prefix ) = Schema::findParts ( $node, $node->getAttribute ( "type" ) );
					
					$type = $this->xsd->findType($ns, $name);

					$this->elements [] = new Element ( $this->xsd, $type, $node->getAttribute ( "name" ), $min, $max,  $nillable);
				}
				
				break;
			default:
				parent::parseElement($node);
		}
	}
} 