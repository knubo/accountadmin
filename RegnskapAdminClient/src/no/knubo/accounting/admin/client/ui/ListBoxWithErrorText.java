package no.knubo.accounting.admin.client.ui;

import no.knubo.accounting.admin.client.validation.Validateable;

import com.google.gwt.event.dom.client.ChangeHandler;
import com.google.gwt.json.client.JSONValue;
import com.google.gwt.user.client.DOM;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.ListBox;

public class ListBoxWithErrorText extends ErrorLabelWidget implements Validateable {

    private ListBox listbox;

    @Override
    public String getText() {
        return Util.getSelected(listbox);
    }

    public ListBoxWithErrorText(String id) {
        super(new ListBox());
        listbox = (ListBox) widget;

        HorizontalPanel hp = new HorizontalPanel();

        hp.add(listbox);
        hp.add(label);
        initWidget(hp);
        DOM.setElementAttribute(listbox.getElement(), "id", id);
    }

    public ListBoxWithErrorText(String id, HTML errorLabel) {
        super(new ListBox(), errorLabel);
        listbox = (ListBox) widget;

        initWidget(listbox);
        DOM.setElementAttribute(listbox.getElement(), "id", id);
    }

    public ListBoxWithErrorText(String id, boolean multiSelect) {
        super(new ListBox(multiSelect));
        listbox = (ListBox) widget;

        HorizontalPanel hp = new HorizontalPanel();

        hp.add(listbox);
        hp.add(label);
        initWidget(hp);
        DOM.setElementAttribute(listbox.getElement(), "id", id);
    }

    public ListBox getListbox() {
        return listbox;
    }

    public void setSelectedIndex(int i) {
        setErrorText("");
        listbox.setSelectedIndex(i);
    }

    public void setIndexByValue(JSONValue value) {
        Util.setIndexByValue(listbox, Util.str(value));
    }

    public void clear() {
        listbox.clear();
    }

    public void addItem(JSONValue description, JSONValue value) {
        listbox.addItem(Util.str(description), Util.str(value));
    }

    public void setVisibleItemCount(int i) {
        listbox.setVisibleItemCount(i);
    }

    public void addChangeHandler(ChangeHandler changeHandler) {
        listbox.addChangeHandler(changeHandler);
    }

    public int getSelectedIndex() {
        return listbox.getSelectedIndex();
    }

    public void addItem(JSONValue jsonValue) {
        String v = Util.str(jsonValue);
        listbox.addItem(v, v);
    }
}
