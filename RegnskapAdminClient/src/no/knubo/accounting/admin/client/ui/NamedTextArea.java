package no.knubo.accounting.admin.client.ui;

import com.google.gwt.user.client.DOM;
import com.google.gwt.user.client.ui.TextArea;

public class NamedTextArea extends TextArea {

    public NamedTextArea(String name) {
        super();
        DOM.setElementAttribute(getElement(), "id", name);
    }
}
