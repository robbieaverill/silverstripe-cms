<?php

class CommentTableField extends ComplexTableField {
	protected $template = "CommentTableField";
	protected $mode;
	
	function __construct($controller, $name, $sourceClass, $mode, $fieldList, $detailFormFields = null, $sourceFilter = "", $sourceSort = "", $sourceJoin = "") {
		$this->mode = $mode;
		
		parent::__construct($controller, $name, $sourceClass, $fieldList, $detailFormFields, $sourceFilter, $sourceSort, $sourceJoin);
		
		$this->Markable = true;
			// search
		$search = isset($_REQUEST['CommentSearch']) ? Convert::raw2sql($_REQUEST['CommentSearch']) : null;
		if(!empty($_REQUEST['CommentSearch'])) {
			$this->sourceFilter[] = "( `Name` LIKE '%$search%' OR `Comment` LIKE '%$search%')";
		}
		
		Requirements::javascript('cms/javascript/CommentTableField.js');
	}
	
	function Items() {
		$this->sourceItems = $this->sourceItems();
		
		if(!$this->sourceItems) {
			return null;
		}
		
		$pageStart = (isset($_REQUEST['ctf'][$this->Name()]['start']) && is_numeric($_REQUEST['ctf'][$this->Name()]['start'])) ? $_REQUEST['ctf'][$this->Name()]['start'] : 0;
		$this->sourceItems->setPageLimits($pageStart, $this->pageSize, $this->totalCount);
		
		$output = new DataObjectSet();
		foreach($this->sourceItems as $pageIndex=>$item) {
			$output->push(Object::create('CommentTableField_Item',$item, $this, $pageStart+$pageIndex));
		}
		return $output;
	}
	
	function spam() {
		if(!Permission::check('ADMIN')) {
			return false;
		}

		$this->methodName = "spam";
		
		$childId = Convert::raw2sql($_REQUEST['tf']['childID']);

		if (is_numeric($childId)) {
			$childObject = DataObject::get_by_id($this->sourceClass, $childId);
			if($childObject) {
				$childObject->IsSpam = true;
				$childObject->NeedsModeration = false;
				$childObject->write();
			}
		}
	}
	
	function ham() {
		if(!Permission::check('ADMIN')) {
			return false;
		}

		$this->methodName = "ham";
		
		$childId = Convert::raw2sql($_REQUEST['tf']['childID']);

		if (is_numeric($childId)) {
			$childObject = DataObject::get_by_id($this->sourceClass, $childId);
			if($childObject) {
				$childObject->IsSpam = false;
				$childObject->NeedsModeration = false;
				$childObject->write();
			}
		}
	}
	
	function accept() {
		if(!Permission::check('ADMIN')) {
			return false;
		}

		$this->methodName = "accept";
		
		$childId = Convert::raw2sql($_REQUEST['tf']['childID']);

		if(is_numeric($childId)) {
			$childObject = DataObject::get_by_id($this->sourceClass, $childId);
			if($childObject) {
				$childObject->IsSpam = false;
				$childObject->NeedsModeration = false;
				$childObject->write();
			}
		}
	}
	
	function HasSpamButton() {
		return $this->mode == 'accepted' || $this->mode == 'unmoderated';
	}
	
	function HasAcceptButton() {
		return $this->mode == 'unmoderated';
	}
	
	function HasHamButton() {
		return $this->mode == 'spam';
	}

	function SearchForm() {
		$searchFields = new FieldGroup(
			new TextField('CommentSearch', 'Search'),
			new HiddenField("ctf[ID]",'',$this->mode),
			new HiddenField('CommentFieldName','',$this->name)
		);
		
		$actionFields = new LiteralField('CommentFilterButton','<input type="submit" name="CommentFilterButton" value="Filter" id="CommentFilterButton"/>');
		
		$fieldContainer = new FieldGroup(
			$searchFields,
			$actionFields
		);
		
		return $fieldContainer->FieldHolder();
	}
}


class CommentTableField_Item extends ComplexTableField_Item {
	function HasSpamButton() {
		return $this->parent()->HasSpamButton();
	}
	
	function HasAcceptButton() {
		return $this->parent()->HasAcceptButton();
	}
	
	function HasHamButton() {
		return $this->parent()->HasHamButton();
	}
	
	function SpamLink() {
		return $this->BaseLink() . "&methodName=spam";
	}
	
	function HamLink() {
		return $this->BaseLink() . "&methodName=ham";
	}
	
	function AcceptLink() {
		return $this->BaseLink() . "&methodName=accept";
	}
}

?>