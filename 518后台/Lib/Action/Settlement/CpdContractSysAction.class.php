<?php
/**
 * 安智网产品管理平台 CPD流量结算控制器2
 * ============================================================================
 * 版权所有 2009-2014 北京力天无限网络有限公司，并保留所有权利
 * cpd
 * ----------------------------------------------------------------------------
 */
include 'CpdContractDepositAction.class.php';
 
class CpdContractSysAction extends CpdContractDepositAction {
	public $flexible_sys;
    function __construct()
    {
        parent::__construct(); 
		$this->flexible_sys = 2;
    }
}