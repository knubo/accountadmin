package no.knubo.accounting.admin.client.misc;

import no.knubo.accounting.admin.client.ui.NamedButton;

import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.user.client.ui.DialogBox;
import com.google.gwt.user.client.ui.DockPanel;
import com.google.gwt.user.client.ui.HTML;

public class MissingDataPopup extends DialogBox implements ClickHandler {

    public MissingDataPopup(String data) {

        HTML html = new HTML("Data mangler for:" + data);

        DockPanel dp = new DockPanel();
        dp.add(html, DockPanel.NORTH);

        NamedButton closeButton = new NamedButton("steng", "steng");
        dp.add(closeButton, DockPanel.NORTH);

        closeButton.addClickHandler(this);

        setText("Data mangler");
        setModal(true);
        setWidget(dp);
    }

    public void onClick(ClickEvent event) {
        hide();
    }
}
