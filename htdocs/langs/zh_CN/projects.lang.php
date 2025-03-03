<?php
/* Copyright (C) 2012	Regis Houssin	<regis.houssin@capnetworks.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

$projects = array(
		'CHARSET' => 'UTF-8',
		'Project' => '项目',
		'Projects' => '项目',
		'SharedProject' => '每个人',
		'PrivateProject' => '项目联系人',
		'MyProjectsDesc' => '这种观点是有限的项目你是一个接触（不管是类型）。',
		'ProjectsPublicDesc' => '这种观点提出了所有你被允许阅读的项目。',
		'ProjectsDesc' => '这种观点提出的所有项目（你的用户权限批准你认为一切）。',
		'MyTasksDesc' => '这种观点是有限的项目或任务你是一个接触（不管是类型）。',
		'TasksPublicDesc' => '这种观点提出的所有项目，您可阅读任务。',
		'TasksDesc' => '这种观点提出的所有项目和任务（您的用户权限批准你认为一切）。',
		'Myprojects' => '我的项目',
		'ProjectsArea' => '项目领域',
		'NewProject' => '新项目',
		'AddProject' => '新增项目',
		'DeleteAProject' => '删除一个项目',
		'DeleteATask' => '删除任务',
		'ConfirmDeleteAProject' => '你确定要删除此项目吗？',
		'ConfirmDeleteATask' => '你确定要删除这个任务吗？',
		'OfficerProject' => '项目主任',
		'LastProjects' => '上次％s的项目',
		'AllProjects' => '所有项目',
		'ProjectsList' => '项目名单',
		'ShowProject' => '显示项目',
		'SetProject' => '设置项目',
		'NoProject' => '没有项目或拥有的定义',
		'NbOpenTasks' => '铌打开任务',
		'NbOfProjects' => '铌项目',
		'TimeSpent' => '花费的时间',
		'TimesSpent' => '所花费的时间',
		'RefTask' => '号。任务',
		'LabelTask' => '标签任务',
		'NewTimeSpent' => '新的时间',
		'MyTimeSpent' => '我的时间花',
		'MyTasks' => '我的任务',
		'Tasks' => '任务',
		'Task' => '任务',
		'NewTask' => '新任务',
		'AddTask' => '新增任务',
		'AddDuration' => '添加时间',
		'Activity' => '活动',
		'Activities' => '任务/活动',
		'MyActivity' => '我的活动',
		'MyActivities' => '我的任务/活动',
		'MyProjects' => '我的项目',
		'DurationEffective' => '有效时间',
		'Progress' => '进展',
		'Time' => '时间',
		'ListProposalsAssociatedProject' => '名单与项目有关的商业建议',
		'ListOrdersAssociatedProject' => '名单与项目相关的客户的订单',
		'ListInvoicesAssociatedProject' => '名单与项目相关的客户的发票',
		'ListPredefinedInvoicesAssociatedProject' => '客户名单的预定义与项目相关的发票',
		'ListSupplierOrdersAssociatedProject' => '名单与项目相关的供应商的订单',
		'ListSupplierInvoicesAssociatedProject' => '名单与项目相关的供应商的发票',
		'ListContractAssociatedProject' => '名单与项目有关的合同',
		'ListFichinterAssociatedProject' => '名单与项目相关的干预措施',
		'ListTripAssociatedProject' => '名单旅行和与项目有关的费用',
		'ListActionsAssociatedProject' => '名单与项目有关的行动',
		'ActivityOnProjectThisWeek' => '对项目活动周',
		'ActivityOnProjectThisMonth' => '本月初对项目活动',
		'ActivityOnProjectThisYear' => '今年对项目活动',
		'ChildOfTask' => '儿童的项目/任务',
		'NotOwnerOfProject' => '不是所有者的私人项目',
		'AffectedTo' => '受影响',
		'CantRemoveProject' => '这个项目不能删除，因为它是由一些（其他对象引用的发票，订单或其他）。见参照资讯标签。',
		'ValidateProject' => '验证谟',
		'ConfirmValidateProject' => '你确定要验证这个项目？',
		'CloseAProject' => '关闭项目',
		'ConfirmCloseAProject' => '你确定要关闭此项目吗？',
		'ReOpenAProject' => '打开的项目',
		'ConfirmReOpenAProject' => '您确定要重新打开这个项目呢？',
		'ProjectContact' => '项目联系人',
		'ActionsOnProject' => '行动项目',
		'YouAreNotContactOfProject' => '你是不是这个私人项目联系',
		'DeleteATimeSpent' => '删除的时间',
		'ConfirmDeleteATimeSpent' => '你确定要删除这个花的时间？',
		'DoNotShowMyTasksOnly' => '又见任务没有影响到我',
		'ShowMyTasksOnly' => '查看任务时，我只受影响',
		'TaskRessourceLinks' => '资源的整合',
		'ProjectsDedicatedToThisThirdParty' => '这个项目致力于第三方',
		'NoTasks' => '该项目没有任务',
		'LinkedToAnotherCompany' => '链接到其他第三方',
		'TaskIsNotAffectedToYou' => '任务不分配给你',
		'ErrorTimeSpentIsEmpty' => '所花费的时间是空的',
		'ThisWillAlsoRemoveTasks' => '这一行动也将删除所有项目任务<b>（%s</b>任务的时刻），花全部的时间都投入。',
		'IfNeedToUseOhterObjectKeepEmpty' => '如果某些对象（发票，订单，...），属于其他第三方，必须与该项目以创建，保持这个空项目多的第三方。',
		'CloneProject' => 'Clone project',
		'CloneTasks' => 'Clone tasks',
		'CloneContacts' => 'Clone contacts',
		'CloneNotes' => 'Clone notes',
		'CloneFiles' => 'Clone joined files',
		'ConfirmCloneProject' => 'Are you sure to clone this project ?',
		'ProjectReportDate' => 'Change task date according project start date',
		'ErrorShiftTaskDate' => 'Impossible to shift task date according to new project start date',
		////////// Types de contacts //////////
		'TypeContact_project_internal_PROJECTLEADER' => '项目负责人',
		'TypeContact_project_external_PROJECTLEADER' => '项目负责人',
		'TypeContact_project_internal_CONTRIBUTOR' => '投稿',
		'TypeContact_project_external_CONTRIBUTOR' => '投稿',
		'TypeContact_project_task_internal_TASKEXECUTIVE' => '执行任务',
		'TypeContact_project_task_external_TASKEXECUTIVE' => '执行任务',
		'TypeContact_project_task_internal_CONTRIBUTOR' => '投稿',
		'TypeContact_project_task_external_CONTRIBUTOR' => '投稿',
		// Documents models
		'DocumentModelBaleine' => '一个完整的项目报告模型（logo. ..）'
);
?>