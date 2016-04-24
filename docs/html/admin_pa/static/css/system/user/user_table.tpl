<% for(var i = 0,len = list.length; i<len; i++) {%>
	<tr>
		<td><%=list[i]['sUmAccount']%></td>
		<td><%=list[i]['sName']%></td>
		<td><%=list[i]['sOrgName']%></td>
		<td><%=list[i]['sRoleList']%></td>
		<td>
			<input type="button" class="btn_default J_user_edit" value="用户编辑" data-iAutoID="<%=list[i]['iAutoID']%>" data-sUmAccount="<%=list[i]['sUmAccount']%>" data-sName="<%=list[i]['sName']%>" data-sMobile="<%=list[i]['sMobile']%>" data-sEmail="<%=list[i]['sEmail']%>" data-iOrgID="<%=list[i]['iOrgID']%>" data-sOrgName="<%=list[i]['sOrgName']%>" data-iUserStatus="<%=list[i]['iUserStatus']%>" data-sRoleIDList="<%=list[i]['sRoleIDList']%>" data-sRoleList="<%=list[i]['sRoleList']%>" />
			<input type="button" class="btn_default J_psword_reset" data-iAutoID="<%=list[i]['iAutoID']%>" value="重置密码" />
		</td>
	</tr>
<% } %>	