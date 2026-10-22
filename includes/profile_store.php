<?php

	function getRecruiterProfile(): ?array
	{
		return $_SESSION['recruiter_profile'] ?? null;
	}

	/**
	 * Save (create or overwrite) the profile data.
	 */
	function saveRecruiterProfile(array $data): void
	{
		$_SESSION['recruiter_profile'] = $data;
	}
?>
