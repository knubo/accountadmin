package no.knubo.accounting.admin.client.ui;

import no.knubo.accounting.admin.client.validation.Validateable;

import com.google.gwt.event.dom.client.KeyUpEvent;
import com.google.gwt.event.dom.client.KeyUpHandler;
import com.google.gwt.user.client.DOM;
import com.google.gwt.user.client.Timer;
import com.google.gwt.user.client.ui.FlowPanel;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.TextBox;
import com.google.gwt.user.client.ui.UIObject;

public class TextBoxWithErrorText extends ErrorLabelWidget implements Validateable {
    public TextBox textBox;

    public TextBoxWithErrorText(String name, HTML errorLabel) {
        super(new TextBox(), errorLabel);
        this.label = errorLabel;
        this.textBox = (TextBox) widget;
        DOM.setElementAttribute(textBox.getElement(), "id", name);

        errorLabel.setStyleName("error");
        initWidget(textBox);
    }

    public TextBoxWithErrorText(String name, boolean flow) {
        super(new TextBox());
        textBox = (TextBox) widget;
        DOM.setElementAttribute(textBox.getElement(), "id", name);

        if (flow) {
            FlowPanel fp = new FlowPanel();

            fp.add(textBox);
            fp.add(label);

            initWidget(fp);
        } else {
            createHorizontalPanel();
        }
    }

    public TextBoxWithErrorText(String name) {
        super(new TextBox());
        textBox = (TextBox) widget;
        DOM.setElementAttribute(textBox.getElement(), "id", name);

        createHorizontalPanel();
    }

    private void createHorizontalPanel() {
        HorizontalPanel hp = new HorizontalPanel();

        hp.add(textBox);
        hp.add(label);

        initWidget(hp);
    }

    public TextBox getTextBox() {
        return textBox;
    }

    /**
     * Sets the text and resets error view.
     * 
     * @param string
     */
    public void setText(String string) {
        label.setText("");
        textBox.setText(string);
    }

    public void setMaxLength(int i) {
        textBox.setMaxLength(i);
    }

    public void setVisibleLength(int i) {
        textBox.setVisibleLength(i);
    }

    @Override
    public String getText() {
        return textBox.getText();
    }

    public void setEnabled(boolean enabled) {
        UIObject.setStyleName(textBox.getElement(), "disabled", !enabled);
        textBox.setEnabled(enabled);
    }

    Timer timer;

    /**
     * The event returned is always null. It is called after 1 second of
     * waiting.
     * 
     * @param handler
     */
    public void addDelayedKeyUpHandler(final KeyUpHandler handler) {
        KeyUpHandler delayedHandler = new KeyUpHandler() {
            public void onKeyUp(KeyUpEvent event) {
                if (timer != null) {
                    return;
                }
                timer = new Timer() {

                    @Override
                    public void run() {
                        handler.onKeyUp(null);
                        timer = null;
                    }

                };
                timer.schedule(1000);
            }
        };
        textBox.addKeyUpHandler(delayedHandler);
    }

    public boolean isEnabled() {
        return textBox.isEnabled();
    }
}
