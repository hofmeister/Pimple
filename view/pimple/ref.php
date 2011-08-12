<html>
    <head>
        <title>Pimple reference</title>
        <p:stylesheet src="css/reset.css" local="false" />
        <p:stylesheet src="css/oocss.css" local="false" />
        <p:stylesheet src="css/skin.css" local="false" />
        <p:stylesheet src="css/structure.css" local="false" />
        <p:stylesheet collect="true" />
        
        <js:include path="js/extended.js" local="false" />
		
        <!-- jquery -->
        <js:include path="js/jquery-1.5.1.min.js" local="false"  minify="false" />
        <js:include path="js/plugins/jquery-json.js" local="false" />
        <js:include path="js/plugins/jquery-fieldselection.js" local="false" />
        <js:include path="js/plugins/jquery-autocomplete.js" local="false" />
        <js:include path="js/plugins/jquery-tokeninput.js" local="false" />
        <js:include path="js/plugins/jquery-qtip.js" local="false" />
        <js:include path="js/plugins/jquery-scrollto.js" local="false" />
		<js:include path="js/plugins/jquery-ui-1.8.5.js" local="false" minify="false" />
        
        <js:include path="js/plugins/date.format.js" local="false" />
        <js:include path="js/pimple.js" local="false" />
        <js:include path="js/utils/range.js" local="false" />
        <js:include path="js/validation.js" local="false" />
        <js:include path="js/widgets/button.js" local="false" />
        <js:include path="js/widgets/formitem.js" local="false" />
        <js:include path="js/widgets/instructions.js" local="false" />
        <js:include path="js/widgets/checker.js" local="false" />
        <js:include path="js/widgets/list.js" local="false" />
        <js:include path="js/widgets/tabpage.js" local="false" />
        <js:include path="js/behaviour/focusclears.js" local="false" />
        <js:include path="js/behaviour/focusselects.js" local="false" />
        <js:include path="js/behaviour/autocomplete.js" local="false" />
        <js:include path="js/behaviour/tokeninput.js" local="false" />
        
        <js:collect />
        
    </head>
    <body>
        <w:panel title="Pimple reference">
            <w:tabPage>
                <w:tabPanel title="Tag libs">
                    <table class="data list">
                        <thead>
                            <th style="width:300px;">Tag</th>
                            <th style="width:300px;">Attributes</th>
                            <th>Description</th>
                            <th style="width:230px;">Method</th>
                        </thead>
                        <tbody>
                            <c:each in="$taglibs" as="$class">
                                <c:if test="$class->getNamespace()">
                                    <c:each in="$class->getTags()" as="$m">
                                        <tr>
                                            <td style="font-size:12px;">
                                            <c:if test="$m->isDeprecated()">  
                                                <span style="text-decoration: line-through;color:red" title="Deprecated">        
                                                    &lt;%{$class->getNamespace()}:%{$m->getTagName()} 
                                                    <c:if test="$m->isContainer()">
                                                        &gt; ... &lt;/%{$class->getNamespace()}:%{$m->getTagName()}&gt 
                                                        <c:else>
                                                            /&gt;
                                                        </c:else>
                                                    </c:if>
                                                </span>
                                                <c:else>
                                                    &lt;%{$class->getNamespace()}:%{$m->getTagName()} 
                                                    <c:if test="$m->isContainer()">
                                                        &gt; ... &lt;/%{$class->getNamespace()}:%{$m->getTagName()}&gt 
                                                        <c:else>
                                                            /&gt;
                                                        </c:else>
                                                    </c:if>
                                                </c:else>
                                            </c:if>
                                                <c:if test="$m->hasDocValue('todo')">
                                                    <br/><b>TODO: %{implode('<br/>',$m->getDocValue('todo'))}</b>
                                                </c:if>
                                            </td>
                                            <td style="font-size: 10px;padding:5px">
                                                <c:each in="$m->getParms()" as="$parm">
                                                    <p><b>%{$parm->name}</b> (%{$parm->type})</p>
                                                    <p style="padding-left: 10px;padding-bottom: 5px;">%{$parm->description}</p>
                                                </c:each>
                                            </td>
                                            <td style="font-size: 10px;">
                                                %{$m->getDescription()}
                                            </td>
                                            <td style="font-size: 10px;">%{$class->name.'::'.$m->name.'()'}</td>
                                        </tr>
                                    </c:each>
                                </c:if>
                            </c:each>
                        </tbody>
                    </table>
                </w:tabPanel>
                <w:tabPanel title="Controllers">
                    <table class="data list">
                        <thead>
                            <th style="width:230px;">URL</th>
                            <th style="width:230px;">Parms</th>
                            <th>Description</th>
                            <th style="width:230px;">Method</th>
                            <th style="width:60px;">Outputs</th>
                        </thead>
                        <tbody>
                            <c:each in="$controllers" as="$class">
                                <c:if test="$class->getUrl()">
                                    <c:each in="$class->methods" as="$m">
                                        <tr>
                                            <td>
                                                /%{$class->getUrl()}/%{$m->getUrl()}/
                                                <div class="js-details" style="width:220px;font-size: 10px;">
                                                    
                                                </div>
                                            </td>
                                            <td style="font-size: 10px;padding:5px;">
                                                <c:each in="$m->getParms()" as="$parm">
                                                    <p><b>%{$parm->name}</b> (%{$parm->type})</p>
                                                    <p style="padding-left: 10px;padding-bottom:5px;">%{$parm->description}</p>
                                                </c:each>
                                            </td>
                                            <td style="font-size: 10px;">%{$m->getDescription()}</td>
                                            <td style="font-size: 10px;">%{$class->name.'::'.$m->name.'()'}</td>
                                            <td style="font-size: 10px;">%{$m->getOutput()}</td>
                                        </tr>
                                    </c:each>
                                </c:if>
                            </c:each>
                        </tbody>
                    </table>
                </w:tabPanel>
            </w:tabPage>
        </w:panel>
    </body>
</html>