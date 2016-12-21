<?php
include_once './includes/WebhookConverter.php';
class BitbucketToDiscordSlackStyleWebhookConverter extends WebhookConverter {
	private $event_type_group = 'unknown';
	private $event_type_name = 'unknown';
	function __construct($event_type) {
		parent::__construct ();
		$event_type_args = explode ( ':', $event_type, 2 );
		if ($event_type_args !== FALSE) {
			if (isset ( $event_type_args [0] ))
				$this->event_type_group = $event_type_args [0];
			if (isset ( $event_type_args [1] ))
				$this->event_type_name = $event_type_args [1];
		}
		$ob = parent::getObjectBuilder ();
		$ob->addAttribute ( 'text', 'BitbucketToDiscordSlackStyleWebhookConverter_buildTextAttribute' );
	}
	public function getEventTypeGroup() {
		return $this->event_type_group;
	}
	public function getEventTypeName() {
		return $this->event_type_name;
	}
	public static function buildTextAttribute($attribute_path, $obj, $event_type_group, $event_type_name) {
		$tag = 'UNKNOWN';
		$title = 'Unknown title';
		$message = 'Unknown message';
		$user_display_name = '';
		$username = 'Unknown user';
		$actor = null;
		$repository = null;
		$repository_name = 'Unknown repository';
		$repository_url = '';
		
		if (isset ( $obj->actor )) {
			$actor = $obj->actor;
			$user_display_name = $actor->display_name;
			$username = $actor->username;
		}
		if (isset ( $obj->repository )) {
			$repository = $obj->repository;
			if (isset ( $repository->name ))
				$repository_name = $repository->name;
			if (isset ( $repository->links )) {
				if (isset ( $repository->links->html )) {
					if (isset ( $repository->links->html->href ))
						$repository_url = $repository->links->html->href;
				}
			}
		}
		switch ($event_type_group) {
			case 'repo' :
				$commit_status = null;
				$commit_status_name = 'UNKNOWN';
				$commit_status_description = 'Unknown description';
				$commit_status_state = 'UNKNOWN';
				$commit_status_url = '';
				$commit_status_bb_url = '';
				if (isset ( $obj->commit_status )) {
					$commit_status = $obj->commit_status;
					if (isset ( $commit_status->name ))
						$commit_status_name = $commit_status->name;
					if (isset ( $commit_status->description ))
						$commit_status_description = $commit_status->description;
					if (isset ( $commit_status->state ))
						$commit_status_state = $commit_status->state;
					if (isset ( $commit_status->url ))
						$commit_status_url = $commit_status->url;
					if (isset ( $commit_status->links )) {
						if (isset ( $commit_status->links->html )) {
							if (isset ( $commit_status->links->html->href ))
								$commit_status_bb_url = $commit_status->links->html->href;
						}
					}
				}
				switch ($event_type_name) {
					case 'push' :
						if (isset ( $obj->push )) {
							// Push
							$push = $obj->push;
							if (isset ( $push->changes )) {
								// Push create
								$changes = $push->changes;
								$tag = 'PUSH';
								$title = 'Push created on repository "' . $repository_name . "\"\n" . $repository_url;
								$message = '';
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
									if (isset ( $change->old )) {
										$change_old = $change->old;
										if (isset ( $change_old->name ))
											$old_branch_name = $change_old->name;
										if (isset ( $change_old->links )) {
											if (isset ( $change_old->links->html )) {
												if (isset ( $change_old->links->html->href ))
													$old_branch_url = $change_old->links->html->href;
											}
										}
										if (isset ( $change_old->target )) {
											$change_old_target = $change_old->target;
											if (isset ( $change_old_target->links )) {
												if (isset ( $change_old_target->links->html )) {
													if (isset ( $change_old_target->links->html->href ))
														$old_commit_url = $change_old_target->links->html->href;
												}
											}
											if (isset ( $change_old_target->message ))
												$old_commit_message = $change_old_target->message;
											unset ( $change_old_target );
										}
									}
									if (isset ( $change->new )) {
										$change_new = $change->new;
										if (isset ( $change_new->name ))
											$new_branch_name = $change_old->name;
										if (isset ( $change_new->links )) {
											if (isset ( $change_new->links->html )) {
												if (isset ( $change_new->links->html->href ))
													$new_branch_url = $change_new->links->html->href;
											}
										}
										if (isset ( $change_new->target )) {
											$change_new_target = $change_new->target;
											if (isset ( $change_new_target->links )) {
												if (isset ( $change_new_target->links->html )) {
													if (isset ( $change_new_target->links->html->href ))
														$new_commit_url = $change_new_target->links->html->href;
												}
											}
											if (isset ( $change_new_target->message ))
												$new_commit_message = $change_new_target->message;
											unset ( $change_new_target );
										}
									}
									if (isset ( $change->links )) {
										if (isset ( $change->links->html )) {
											if (isset ( $change->links->html->href ))
												$commit_diff_url = $change->links->html->href;
										}
									}
									$message .= ($first ? '' : "\n\n") . '(' . trim ( $old_commit_message ) . ') ->  (' . trim ( $new_commit_message ) . ")\n" . $old_commit_url . ' -> ' . $new_commit_url . "\n\nBranch: " . ' (' . $old_branch_name . ') -> (' . $new_branch_name . ")\n" . $old_branch_url . ' -> ' . $new_branch_url . "\n\n" . "\nDifference: " . $commit_diff_url;
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
						}
						break;
					case 'fork' :
						if (isset ( $obj->fork )) {
							// Fork
							$fork = $obj->fork;
							$tag = 'FORK';
							$title = 'Fork created from repository "' . $repository_name . "\"\n" . $repository_url;
							$fork_name = 'Unknown name';
							$fork_language = 'Unknown language';
							$fork_description = 'Unknown description';
							$fork_url = '';
							if (isset ( $fork->name ))
								$fork_name = $fork->name;
							if (isset ( $fork->language ))
								$fork_language = $fork->language;
							if (isset ( $fork->description ))
								$fork_description = $fork->description;
							if (isset ( $fork->links )) {
								if (isset ( $fork->links->html )) {
									if (isset ( $fork->links->html->href ))
										$fork_url = $fork->links->html->href;
								}
							}
							$message = 'Fork "' . $fork_name . "\" has been created.\n\nLanguage: " . $fork_language . "\n\nDescription: " . $fork_description . "\n\nURL: " . $fork_url;
							unset ( $fork_name );
							unset ( $fork_language );
							unset ( $fork_description );
							unset ( $fork_url );
							unset ( $fork );
						}
						break;
					case 'updated' :
						if (isset ( $obj->changes )) {
							// Repository update
							$changes = $obj->changes;
							$tag = 'UPDATE';
							$title = 'Repository update on repository "' . $repository_name . "\"\n" . $repository_url;
							$message = '```';
							$changes_old = '';
							$changes_new = '';
							$first = true;
							foreach ( $changes as $k => $v ) {
								if ($first)
									$first = false;
								else
									$message .= "\n\n";
								$changes_old = 'Unknown';
								$changes_new = 'Unknown';
								if (isset ( $v->old ))
									$changes_old = $v->old;
								if (isset ( $v->new ))
									$changes_new = $v->new;
								$message .= '"' . $k . "\"\n\t\"" . $changes_old . '" -> "' . $changes_new . '"';
							}
							$message .= '```';
							unset ( $changes_old );
							unset ( $changes_new );
							unset ( $first );
							unset ( $changes );
						}
						break;
					case 'commit_comment_created' :
						if (isset ( $obj->commit ) && isset ( $obj->comment )) {
							// Commit comment
							$commit = $obj->commit;
							$comment = $obj->comment;
							$tag = 'COMMENT';
							$title = 'Commit comment added on repository "' . $repository_name . "\" added\n" . $repository_url;
							$commit_message = 'Unknown commit';
							$comment_message = 'Unknown commit comment';
							$comment_url = '';
							if (isset ( $commit->message ))
								$commit_message = $commit->message;
							if (isset ( $comment->content )) {
								if (isset ( $comment->content->raw ))
									$comment_message = $comment->content->raw;
							}
							if (isset ( $comment->links )) {
								if (isset ( $comment->links->html )) {
									if (isset ( $comment->links->html->href ))
										$comment_url = $comment->links->html->href;
								}
							}
							$message = 'On commit "' . trim ( $commit_message ) . "\":\n" . $comment_message . "\n\n" . $comment_url;
							unset ( $commit_message );
							unset ( $comment_message );
							unset ( $comment_url );
							unset ( $commit );
							unset ( $comment );
						}
						break;
					case 'commit_status_created' :
						$tag = 'BUILD-CREATE';
						$title = 'Build created on ' . $commit_status_name . ' (' . $commit_status_description . ')';
						$message = 'Build state "' . $commit_status_state . "\"\n" . $commit_status_url . "\n" . $commit_status_bb_url;
						break;
					case 'commit_status_updated' :
						$tag = 'BUILD-UPDATE';
						$title = 'Build updated on ' . $commit_status_name . ' (' . $commit_status_description . ')';
						$message = 'Build state "' . $commit_status_state . "\"\n" . $commit_status_url . "\n" . $commit_status_bb_url;
						break;
				}
				unset ( $commit_status_name );
				unset ( $commit_status_description );
				unset ( $commit_status_state );
				unset ( $commit_status_url );
				unset ( $commit_status_bb_url );
				unset ( $commit_status );
				break;
			case 'pullrequest' :
				if (isset ( $obj->pullrequest )) {
					// Pull request
					$pull_request = $obj->pullrequest;
					$pull_request_id = 0;
					$pull_request_title = 'Unknown title';
					$pull_request_branch = 'Unknown branch';
					$pull_request_state = '';
					$pull_request_url = '';
					$pull_request_comment = null;
					$pull_request_comment_id = 0;
					$pull_request_comment_message = 'Unknown message';
					$pull_request_comment_url = '';
					if (isset ( $pull_request->id ))
						$pull_request_id = $pull_request->id;
					if (isset ( $pull_request->title ))
						$pull_request_title = $pull_request->title;
					if (isset ( $pull_request->source )) {
						if (isset ( $pull_request->source->branch ))
							$pull_request_branch = $pull_request->source->branch;
					}
					if (isset ( $pull_request->state ))
						$pull_request_state = $pull_request->state;
					$pull_request_title = $pull_request->title;
					if (isset ( $pull_request->links )) {
						if (isset ( $pull_request->links->html )) {
							if (isset ( $pull_request->links->html->href ))
								$pull_request_url = $pull_request->links->html->href;
						}
					}
					if (isset ( $obj->comment )) {
						// Comment create or delete
						$pull_request_comment = $obj->comment;
						if (isset ( $pull_request_comment->id ))
							$pull_request_comment_id = $pull_request_comment->id;
						if (isset ( $pull_request_comment->content )) {
							if (isset ( $pull_request_comment->content->raw ))
								$pull_request_comment_message = $pull_request_comment->content->raw;
						}
						if (isset ( $pull_request_comment->links )) {
							if (isset ( $pull_request_comment->links->html )) {
								if (isset ( $pull_request_comment->links->html->raw ))
									$pull_request_comment_url = $pull_request_comment->links->html->raw;
							}
						}
					}
					switch ($event_type_name) {
						case 'created' :
							$tag = 'CREATE';
							$title = 'Pull request #' . $pull_request_id . ' (' . $pull_request_title . ")\n" . $pull_request_url;
							$message = 'Pull request has been created';
							break;
						case 'updated' :
							$tag = 'UPDATE';
							$title = 'Pull request update #' . $pull_request_id . ' (' . $pull_request_title . ")\n" . $pull_request_url;
							$message = 'Pull request state updated to ' . strtoupper ( $pull_request_state );
							break;
						case 'approved' :
							$tag = 'APPROVAL';
							$title = 'Pull request approved on #' . $pull_request_id . ' (' . $pull_request_title . ")\n" . $pull_request_url;
							$message = 'Pull request approved';
							break;
						case 'unapproved' :
							$tag = 'UNAPPROVAL';
							$title = 'Pull request unapproved on #' . $pull_request_id . ' (' . $pull_request_title . ")\n" . $pull_request_url;
							$message = 'Pull request approval removed';
							break;
						case 'fulfilled' :
							$tag = 'FULFILLED';
							$title = 'Pull request fulfilled on #' . $pull_request_id . ' (' . $pull_request_title . ")\n" . $pull_request_url;
							$message = 'Pull request fulfilled';
							break;
						case 'rejected' :
							$tag = 'REJECTED';
							$title = 'Pull request rejected on #' . $pull_request_id . ' (' . $pull_request_title . ")\n" . $pull_request_url;
							$message = 'Pull request rejected';
							break;
						case 'comment_created' :
							$tag = 'COMMENT-CREATE';
							$title = 'Pull request comment created on #' . $pull_request_id . ' (' . $pull_request_title . ")\n" . $pull_request_url;
							$message = '#' . $pull_request_comment_id . ' -> ' . $pull_request_comment_url . ': ' . $pull_request_comment_message;
							break;
						case 'comment_updated' :
							$tag = 'COMMENT-UPDATE';
							$title = 'Pull request comment updated on #' . $pull_request_id . ' (' . $pull_request_title . ")\n" . $pull_request_url;
							$message = '#' . $pull_request_comment_id . ' -> ' . $pull_request_comment_url . ': ' . $pull_request_comment_message;
							break;
						case 'comment_deleted' :
							$tag = 'COMMENT-DELETE';
							$title = 'Pull request comment deleted on #' . $pull_request_id . ' (' . $pull_request_title . ")\n" . $pull_request_url;
							$message = '#' . $pull_request_comment_id . ' -> ' . $pull_request_comment_url . ': ' . $pull_request_comment_message;
							break;
					}
					unset ( $pull_request_comment_message );
					unset ( $pull_request_comment_id );
					unset ( $pull_request_comment );
					unset ( $pull_request_id );
					unset ( $pull_request_title );
					unset ( $pull_request_branch );
					unset ( $pull_request_state );
					unset ( $pull_request_url );
					unset ( $pull_request );
				}
				break;
			case 'issue' :
				if (isset ( $obj->issue )) {
					// Issue
					$issue = $obj->issue;
					$issue_id = 0;
					$issue_version = '0.0.0.0';
					$issue_kind = 'Unknown';
					$issue_title = 'Unknown title';
					$issue_message = 'Unknown message';
					$issue_url = '';
					$comment = null;
					$comment_id = 0;
					$comment_message = 'Unknown comment message';
					$comment_url = '';
					if (isset ( $issue->id ))
						$issue_id = $issue->id;
					if (isset ( $issue->kind ))
						$issue_kind = $issue->kind;
					if (isset ( $issue->title ))
						$issue_title = $issue->title;
					if (isset ( $issue->version ))
						$issue_version = $issue->version;
					if (isset ( $issue->content )) {
						if (isset ( $issue->content->raw ))
							$issue_message = $issue->content->raw;
					}
					if (isset ( $issue->links )) {
						if (isset ( $issue->links->html )) {
							if (isset ( $issue->links->html->href ))
								$issue_url = $issue->links->html->href;
						}
					}
					if (isset ( $obj->comment )) {
						// Create comment or update
						$comment = $obj->comment;
						if (isset ( $comment->id ))
							$comment_id = $comment->id;
						if (isset ( $comment->content )) {
							if (isset ( $comment->content->raw ))
								$comment_message = $comment->content->raw;
						}
						if (isset ( $comment->links )) {
							if (isset ( $comment->links->html )) {
								if (isset ( $comment->links->html->self ))
									$comment_url = $comment->links->html->self;
							}
						}
					}
					switch ($event_type_name) {
						case 'created' :
							// Create
							$tag = 'CREATE';
							$title = 'Issue #' . $issue_id . ' created on repository "' . $repository_name . "\"\n" . $repository_url;
							$message = 'Version: ' . $issue_version . "\n[" . strtoupper ( $issue_kind ) . '] ' . $issue_title . "\n\n" . $issue_message . "\n\n" . $issue_url;
							break;
						case 'updated' :
							if (isset ( $obj->changes )) {
								// Update
								$changes = $obj->changes;
								$tag = 'UPDATE';
								$title = 'Issue status updated on issue #' . $issue_id . ' on repository "' . $repository_name . "\"\n" . $repository_url;
								$changes_status_old = 'Unknown status';
								$changes_status_new = 'Unknown status';
								if (isset ( $changes->status )) {
									$changes_status = $changes->status;
									if (isset ( $changes_status->old ))
										$changes_status_old = $changes_status->old;
									if (isset ( $changes_status->new ))
										$changes_status_new = $changes_status->new;
								}
								$message = 'From "' . $changes_status_old . '\" to \"' . $changes_status_new . "\".\n\n" . $issue_url;
							}
							break;
						case 'comment_created' :
							// Create comment
							$tag = 'COMMENT';
							$title = 'Comment created on issue #' . $issue_id . ' on repository "' . $repository_name . "\"\n" . $repository_url;
							$message = $comment_message . "\n\n" . $comment_url . "\n\n" . $issue_url;
							break;
					}
					unset ( $comment );
					unset ( $comment_id );
					unset ( $comment_message );
					unset ( $comment_url );
				}
				break;
		}
		unset ( $repository_name );
		unset ( $repository_url );
		unset ( $actor );
		unset ( $repository );
		return '[' . $tag . '] ' . $title . "\nBy " . $user_display_name . ' (' . $username . ")\n\n" . $message;
	}
}
function BitbucketToDiscordSlackStyleWebhookConverter_buildTextAttribute($attribute_path, $obj, $ref) {
	return BitbucketToDiscordSlackStyleWebhookConverter::buildTextAttribute ( $attribute_path, $obj, $ref->getEventTypeGroup (), $ref->getEVentTypeName () );
}
?>