<?php

namespace TenStreet\Entity\ApplicationData;

class ApplicationData {
	
	protected $AppReferrer;
	
	protected $Licenses;
	
	protected $DisplayFields;

	public function getArrayCopy()
	{
		$array = get_object_vars($this);
		return $array;
	}

	/**
	 *
	 * @return the $AppReferrer
	 */
	public function getAppReferrer()
	{
		return $this->AppReferrer;
	}

	/**
	 *
	 * @param field_type $AppReferrer        	
	 */
	public function setAppReferrer($AppReferrer)
	{
		$this->AppReferrer = $AppReferrer;
		return $this;
	}

	/**
	 *
	 * @return the $Licenses
	 */
	public function getLicenses()
	{
		return $this->Licenses;
	}

	/**
	 *
	 * @param field_type $Licenses        	
	 *
	 * @return ApplicationData
	 */
	public function setLicenses($Licenses)
	{
		$this->Licenses = $Licenses;
		return $this;
	}

	/**
	 *
	 * @return array
	 */
	public function getDisplayFields()
	{
		return $this->DisplayFields;
	}

	/**
	 *
	 * @param array $DisplayFields        	
	 */
	public function setDisplayFields($DisplayFields)
	{
		$this->DisplayFields = $DisplayFields;
		return $this;
	}

	/**
	 *
	 * @param \TenStreet\Entity\ApplicationData\DisplayField $DisplayField        	
	 */
	public function addDisplayFields($DisplayField)
	{
		$this->DisplayFields [] = $DisplayField;
		return $this;
	}
}