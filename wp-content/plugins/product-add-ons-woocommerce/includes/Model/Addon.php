<?php

namespace ZAddons\Model;

class Addon
{
		private $title;
		private $description;
		private $namespace;
		private $link;

		public function __construct($title, $description, $namespace, $link)
		{
				$this->title = $title;
				$this->description = $description;
				$this->namespace = $namespace;
				$this->link = $link;
		}

		public function getTitle() {
				return $this->title;
		}

		public function getDescription() {
				return $this->description;
		}

		public function getNamespace() {
				return $this->namespace;
		}

		public function getLink() {
				return $this->link;
		}
}
