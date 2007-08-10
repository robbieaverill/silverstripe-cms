<?php

class FeedbackAdmin extends LeftAndMain {
	
	public function init() {
		parent::init();
		
		Requirements::javascript("cms/javascript/FeedbackAdmin_right.js");
	}
	
	public function Link($action = null) {
		return "admin/feedback/$action";
	}
	
	public function showtable($params) {
	    return $this->getLastFormIn($this->renderWith('FeedbackAdmin_right'));
	}
	
	public function EditForm() {
		$url = rtrim($_SERVER['REQUEST_URI'], '/');
		if(strrpos($url, '&')) {
			$url = substr($url, 0, strrpos($url, '&'));
		}
		$section = substr($url, strrpos($url, '/') + 1);
		
		if($section != 'accepted' && $section != 'unmoderated' && $section != 'spam') {
			$section = 'accepted';
		}
		
		if($section == 'accepted') {
			$filter = 'IsSpam=0 AND NeedsModeration=0';
			$title = "<h2>Accepted Comments</h2>";
		} else if($section == 'unmoderated') {
			$filter = 'NeedsModeration=1';
			$title = "<h2>Comments Awaiting Moderation</h2>";
		} else {
			$filter = 'IsSpam=1';
			$title = "<h2>Spam</h2>";
		}
		
		$tableFields = array(
			"Name" => "Author",
			"Comment" => "Comment",
			"PageTitle" => "Page"
		);	
		
		$popupFields = new FieldSet(
			new TextField("Name"),
			new TextareaField("Comment", "Comment")
		);
		
		$idField = new HiddenField('ID', '', $section);
		$table = new CommentTableField($this, "Comments", "PageComment", $section, $tableFields, $popupFields, array($filter));
		$table->setParentClass(false);
		
		$fields = new FieldSet(new LiteralField("Title", $title), $idField, $table);
		
		$actions = new FieldSet();
		
		if($section == 'unmoderated') {
			$actions->push(new FormAction('acceptmarked', 'Accept'));
		}
		
		if($section == 'accepted' || $section == 'unmoderated') {
			$actions->push(new FormAction('spammarked', 'Mark as spam'));
		}
		
		if($section == 'spam') {
			$actions->push(new FormAction('hammarked', 'Mark as not spam'));
		}
		
		$actions->push(new FormAction('deletemarked', 'Delete'));
		
		$form = new Form($this, "EditForm", $fields, $actions);
		
		return $form;
	}
	
	function deletemarked() {
			$numComments = 0;
			$folderID = 0;
			$deleteList = '';
	
			if($_REQUEST['Comments']) {
				foreach($_REQUEST['Comments'] as $commentid) {
					$comment = DataObject::get_one('PageComment', "`PageComment`.ID = $commentid");
					if($comment) {
						$comment->delete();
						$numComments++;
					}
				}
			} else {
				user_error("No comments in $commentList could be found!", E_USER_ERROR);
			}
		
			echo <<<JS
				$deleteList
				$('Form_EditForm').getPageFromServer($('Form_EditForm_ID').value);
				statusMessage("Deleted $numComments comments.");
JS;
	}
	
	function spammarked() {
			$numComments = 0;
			$folderID = 0;
			$deleteList = '';
	
			if($_REQUEST['Comments']) {
				foreach($_REQUEST['Comments'] as $commentid) {
					$comment = DataObject::get_one('PageComment', "`PageComment`.ID = $commentid");
					if($comment) {
						$comment->IsSpam = true;
						$comment->NeedsModeration = false;
						$comment->write();
						$numComments++;
					}
				}
			} else {
				user_error("No comments in $commentList could be found!", E_USER_ERROR);
			}
		
			echo <<<JS
				$deleteList
				$('Form_EditForm').getPageFromServer($('Form_EditForm_ID').value);
				statusMessage("Marked $numComments comments as spam.");
JS;
	}
	
	function hammarked() {
			$numComments = 0;
			$folderID = 0;
			$deleteList = '';
	
			if($_REQUEST['Comments']) {
				foreach($_REQUEST['Comments'] as $commentid) {
					$comment = DataObject::get_one('PageComment', "`PageComment`.ID = $commentid");
					if($comment) {
						$comment->IsSpam = false;
						$comment->NeedsModeration = false;
						$comment->write();
						$numComments++;
					}
				}
			} else {
				user_error("No comments in $commentList could be found!", E_USER_ERROR);
			}
		
			echo <<<JS
				$deleteList
				$('Form_EditForm').getPageFromServer($('Form_EditForm_ID').value);
				statusMessage("Marked $numComments comments as not spam.");
JS;
	}
	
	function acceptmarked() {
			$numComments = 0;
			$folderID = 0;
			$deleteList = '';
	
			if($_REQUEST['Comments']) {
				foreach($_REQUEST['Comments'] as $commentid) {
					$comment = DataObject::get_one('PageComment', "`PageComment`.ID = $commentid");
					if($comment) {
						$comment->IsSpam = false;
						$comment->NeedsModeration = false;
						$comment->write();
						$numComments++;
					}
				}
			} else {
				user_error("No comments in $commentList could be found!", E_USER_ERROR);
			}
		
			echo <<<JS
				$deleteList
				$('Form_EditForm').getPageFromServer($('Form_EditForm_ID').value);
				statusMessage("Accepted $numComments comments.");
JS;
	}
}

?>