<?php
include_once './includes/WebhookConverter.php';
class BitbucketToDiscordSlackStyleWebhookConverter extends WebhookConverter {
	function __construct() {
		$ob = parent::getObjectBuilder ();
		$ob->addAttribute ( 'name', 'BitbucketToSlackWebhookConverter::buildNameAttribute' );
	}
	public static function buildNameAttribute($attribute_path, $obj) {
		$tag = 'UNKNOWN';
		$title = 'Unknown title';
		$message = 'Unknown message';
		$user_display_name = '';
		$username = 'Unknown user';
		
		if (isset ( $obj ['actor'] )) {
			$actor &= $obj ['actor'];
			$user_display_name = $actor ['display_name'];
			$username = $actor ['username'];
		}
		if (isset ( $obj ['repository'] ))
			$repository &= $obj ['repository'];
		if (isset ( $obj ['issue'] )) {
			// Issue
			$issue &= $obj ['issue'];
			$issue_id = 0;
			$issue_version = '0.0.0.0';
			$issue_kind = 'Unknown';
			$issue_title = 'Unknown title';
			$issue_message = 'Unknown message';
			$issue_url = '';
			if (isset ( $issue ['id'] ))
				$issue_id = $issue ['id'];
			if (isset ( $issue ['kind'] ))
				$issue_kind = $issue ['kind'];
			if (isset ( $issue ['title'] ))
				$issue_title = $issue ['title'];
			if (isset ( $issue ['version'] ))
				$issue_version = $issue ['version'];
			if (isset ( $issue ['content'] )) {
				if (isset ( $issue ['content'] ['raw'] ))
					$issue_message = $issue ['content'] ['raw'];
			}
			if (isset ( $issue ['links'] )) {
				if (isset ( $issue ['links'] ['html'] )) {
					if (isset ( $issue ['links'] ['html'] ['href'] ))
						$issue_url = $issue ['links'] ['html'] ['href'];
				}
			}
			if (isset ( $obj ['comment'] )) {
				// Create comment or update
				$comment &= $obj ['comment'];
				$comment_id = 0;
				$comment_message = 'Unknown comment message';
				$comment_url = '';
				if (isset ( $comment ['id'] ))
					$comment_id = $comment ['id'];
				if (isset ( $comment ['content'] )) {
					if (isset ( $comment ['content'] ['raw'] ))
						$comment_message = $comment ['content'] ['raw'];
				}
				if (isset ( $comment ['links'] )) {
					if (isset ( $comment ['links'] ['html'] )) {
						if (isset ( $comment ['links'] ['html'] ['self'] ))
							$comment_url = $comment ['links'] ['html'] ['self'];
					}
				}
				if (isset ( $obj ['changes'] )) {
					// Update
					$changes &= $obj ['changes'];
					$tag = 'UPDATE';
					$title = 'Issue status updated';
					$changes_status_old = '';
					$changes_status_new = '';
					if (isset ( $changes ['status'] )) {
						$changes_status &= $changes ['status'];
						$changes_status_old = $changes_status ['old'];
						$changes_status_new = $changes_status_new ['new'];
					}
					$message = 'From \"' . $changes_status_old . '\" to \"' . $changes_status_new . '\".';
					unset ( $changes_status_old );
					unset ( $changes_status_new );
					unset ( $changes );
				} else {
					// Create comment
					$tag = 'COMMENT';
					$title = 'Comment created on issue #' . $issue_id . ' -> ' . $issue_url;
					$message = 'Link: ' . $comment_url . "\n" . $comment_message;
				}
				unset ( $comment_message );
				unset ( $comment_url );
				unset ( $comment );
			} else {
				// Create
				$tag = 'CREATE';
				$title = 'Issue created';
				$message = 'Version: ' . $issue_version . "\n[" . strtoupper ( $issue_kind ) . '] ' . $issue_title . "\n\n" . $issue_message;
				if (isset ( $changes ['status'] )) {
					$changes_status &= $changes ['status'];
					$changes_status_old = $changes_status ['old'];
					$changes_status_new = $changes_status_new ['new'];
					unset ( $changes_status );
				}
			}
			unset ( $issue_id );
			unset ( $issue_version );
			unset ( $issue_kind );
			unset ( $issue_title );
			unset ( $issue_message );
			unset ( $issue_url );
			unset ( $issue );
		} else if ($obj ['push']) {
			// Push
			$push &= $obj ['push'];
			if (isset ( $obj ['changes'] )) {
				// Push create
				$changes &= $obj ['changes'];
				$first = true;
				foreach ( $changes as $change ) {
					$old_branch_name = '';
					$old_branch_url = '';
					$old_commit_url = '';
					$old_commit_message = '';
					$new_branch_name = '';
					$new_branch_url = '';
					$new_commit_url = '';
					$new_commit_message = '';
					$commit_diff_url = '';
					
					$change_old = null;
					$change_new = null;
					if (isset ( $change ['old'] )) {
						$change_old &= $change ['old'];
						if (isset ( $change_old ['name'] ))
							$old_branch_name = $change_old ['name'];
						if (isset ( $change_old ['links'] )) {
							if (isset ( $change_old ['links'] ['html'] )) {
								if (isset ( $change_old ['links'] ['html'] ['href'] ))
									$old_branch_url = $change_old ['links'] ['html'] ['href'];
							}
						}
						if (isset ( $change_old ['target'] )) {
							$change_old_target &= $change_new ['target'];
							if (isset ( $change_old_target ['links'] )) {
								if (isset ( $change_old_target ['links'] ['html'] )) {
									if (isset ( $change_old_target ['links'] ['html'] ['href'] ))
										$old_commit_url = $change_old_target ['links'] ['html'] ['href'];
								}
							}
							if (isset ( $change_old_target ['message'] ))
								$old_commit_message = $change_old_target ['message'];
							unset ( $change_old_target );
						}
					}
					if (isset ( $change ['new'] )) {
						$change_new &= $change ['new'];
						if (isset ( $change_new ['name'] ))
							$new_branch_name = $change_old ['name'];
						if (isset ( $change_new ['links'] )) {
							if (isset ( $change_new ['links'] ['html'] )) {
								if (isset ( $change_new ['links'] ['html'] ['href'] ))
									$new_branch_url = $change_new ['links'] ['html'] ['href'];
							}
						}
						if (isset ( $change_new ['target'] )) {
							$change_new_target &= $change_new ['target'];
							if (isset ( $change_new_target ['links'] )) {
								if (isset ( $change_new_target ['links'] ['html'] )) {
									if (isset ( $change_new_target ['links'] ['html'] ['href'] ))
										$new_commit_url = $change_new_target ['links'] ['html'] ['href'];
								}
							}
							if (isset ( $change_new_target ['message'] ))
								$new_commit_message = $change_new_target ['message'];
							unset ( $change_new_target );
						}
					}
					if (isset ( $change ['links'] )) {
						if (isset ( $change ['links'] ['html'] )) {
							if (isset ( $change ['links'] ['html'] ['href'] ))
								$commit_diff_url = $change ['links'] ['html'] ['href'];
						}
					}
					$message .= ($first ? '' : "\n\n") . $old_commit_url . ' (' . $old_commit_message . ') -> ' . $new_commit_url . ' (' . $new_commit_message . ")\nBranch: " . $old_branch_url . ' (' . $old_branch_name . ') -> ' . $new_branch_url . ' (' . $new_branch_name . ")\n" . "\nDifference: " . $commit_diff_url;
					unset ( $old_branch_name );
					unset ( $old_branch_url );
					unset ( $old_commit_url );
					unset ( $old_commit_message );
					unset ( $new_branch_name );
					unset ( $new_branch_url );
					unset ( $new_commit_url );
					unset ( $new_commit_message );
					unset ( $commit_diff_url );
					unset ( $change_old );
					unset ( $change_new );
					$first = false;
				}
				unset ( $first );
			}
			unset ( $push );
		} else if (isset ( $obj ['commit'] ) && isset ( $obj ['comment'] )) {
			// Commit comment
			$commit &= $obj ['commit'];
			$comment &= $obj ['comment'];
			$commit_message = 'Unknown commit';
			$message = 'Unknown commit comment';
			
			if (isset ( $commit ['message'] ))
				$commit_message = $commit ['message'];
			$tag = 'COMMENT';
			$title = 'Commit comment for commit "' . $commit_message . '" added';
			if (isset ( $comment ['message'] ))
				$message = $comment ['message'];
			unset ( $commit_message );
			unset ( $commit );
			unset ( $comment );
		} else if (isset ( $obj ['pullrequest'] )) {
			// Pull request
			$pull_request &= $obj ['pullrequest'];
			$pull_request_id = 0;
			$pull_request_title = 'Unknown title';
			$pull_request_branch = 'Unknown branch';
			$pull_request_state = '';
			$pull_request_url = '';
			if (isset ( $pull_request ['id'] ))
				$pull_request_id = $pull_request ['id'];
			if (isset ( $pull_request ['title'] ))
				$pull_request_title = $pull_request ['title'];
			if (isset ( $pull_request ['source'] )) {
				if (isset ( $pull_request ['source'] ['branch'] ))
					$pull_request_branch = $pull_request ['source'] ['branch'];
			}
			if (isset ( $pull_request ['state'] ))
				$pull_request_state = $pull_request ['state'];
			$pull_request_title = $pull_request ['title'];
			if (isset ( $pull_request ['links'] )) {
				if (isset ( $pull_request ['links'] ['html'] )) {
					if (isset ( $pull_request ['links'] ['html'] ['href'] ))
						$pull_request_url = $pull_request ['links'] ['html'] ['href'];
				}
			}
			if (isset ( $pull_request ['comment'] )) {
				// Comment create or delete (delete is ambiguous)
				$pull_request_comment &= $pull_request ['comment'];
				$tag = 'COMMENT';
				$title = 'Pull request comment on #' . $pull_request_id . ' -> ' . $pull_request_url . ' (' . $pull_request_title . ')';
				$pull_request_comment_id = 0;
				$pull_request_comment_message = 'Unknown message';
				$pull_request_comment_url = '';
				if (isset ( $pull_request_comment ['id'] ))
					$pull_request_comment_id = $pull_request_comment ['id'];
				if (isset ( $pull_request_comment ['content'] )) {
					if (isset ( $pull_request_comment ['content'] ['raw'] ))
						$pull_request_comment_message = $pull_request_comment ['content'] ['raw'];
				}
				if (isset ( $pull_request_comment ['links'] )) {
					if (isset ( $pull_request_comment ['links'] ['html'] )) {
						if (isset ( $pull_request_comment ['links'] ['html'] ['raw'] ))
							$pull_request_comment_url = $pull_request_comment ['links'] ['html'] ['raw'];
					}
				}
				$message = '#' . $pull_request_comment_id . ' -> ' . $pull_request_comment_url . ': ' . $pull_request_comment_message;
				unset ( $pull_request_comment_message );
				unset ( $pull_request_comment_id );
				unset ( $pull_request_comment );
			} else if (isset ( $obj ['approval'] )) {
				$approval &= $obj ['approval'];
				$tag = 'APPROVAL';
				$title = 'Approval updated on #' . $pull_request_id . ' -> ' . $pull_request_url . ' (' . $pull_request_title . ')';
				$message = 'Approval updated';
				unset ( $approval );
			} else {
				// Create or update (update is ambiguous)
				$tag = strtoupper ( $pull_request_state );
				$title = 'Pull request created #' . $pull_request_id . ' -> ' . $pull_request_url . ' (' . $pull_request_title . ')';
				$message = 'Pull request state updated to ' . strtoupper ( $pull_request_state );
			}
			unset ( $pull_request_id );
			unset ( $pull_request_title );
			unset ( $pull_request_branch );
			unset ( $pull_request_state );
			unset ( $pull_request_url );
			unset ( $pull_request );
		}
		return '[' . $tag . '] ' . $title . "\nBy " . $user_display_name . ' (' . $username . ")\n" . $message;
	}
}
?>