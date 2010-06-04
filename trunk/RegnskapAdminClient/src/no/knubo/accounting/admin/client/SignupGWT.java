package no.knubo.accounting.admin.client;

import java.util.HashMap;
import java.util.List;
import java.util.Set;
import java.util.Map.Entry;

import no.knubo.accounting.admin.client.misc.AdminAuthResponder;
import no.knubo.accounting.admin.client.misc.ServerResponse;
import no.knubo.accounting.admin.client.misc.ServerResponseWithValidation;
import no.knubo.accounting.admin.client.ui.NamedButton;
import no.knubo.accounting.admin.client.ui.TextBoxWithErrorText;
import no.knubo.accounting.admin.client.ui.Util;
import no.knubo.accounting.admin.client.validation.MasterValidator;

import com.google.gwt.core.client.EntryPoint;
import com.google.gwt.event.dom.client.ClickEvent;
import com.google.gwt.event.dom.client.ClickHandler;
import com.google.gwt.json.client.JSONArray;
import com.google.gwt.json.client.JSONObject;
import com.google.gwt.json.client.JSONValue;
import com.google.gwt.user.client.Timer;
import com.google.gwt.user.client.ui.Anchor;
import com.google.gwt.user.client.ui.DecoratedTabPanel;
import com.google.gwt.user.client.ui.FlexTable;
import com.google.gwt.user.client.ui.FlowPanel;
import com.google.gwt.user.client.ui.HTML;
import com.google.gwt.user.client.ui.RootPanel;
import com.google.gwt.user.client.ui.Widget;

/**
 * Entry point classes define <code>onModuleLoad()</code>.
 */
public class SignupGWT implements EntryPoint, ClickHandler {

    // private FlexTable userTable;
    private FlexTable registerTable;
    private NamedButton registerButton;
    private NamedButton cancelButton;
    private HashMap<String, TextBoxWithErrorText> nameGivesWidget;
    private DecoratedTabPanel tabPanel;
    private Status status;

    enum Status {
        UNREGISTERED, PENDING_OR_COMPLETE, FULL_DB;
    }

    /**
     * This is the entry point method.
     */
    public void onModuleLoad() {
        tabPanel = new DecoratedTabPanel();

        registerTable = new FlexTable();

        RootPanel.get("signupapp").add(tabPanel);

        Timer timer = new Timer() {

            @Override
            public void run() {
                init();
            }
        };
        timer.schedule(1 * 1000);

    }

    private void init() {
        calculateStatus();
    }

    private JSONArray installations;

    private void calculateStatus() {
        ServerResponseWithValidation callback = new ServerResponseWithValidation() {

            public void serverResponse(JSONValue responseObj) {
                installations = responseObj.isArray();
                if (installations == null || installations.size() == 0) {
                    status = Status.UNREGISTERED;
                } else {
                    status = Status.PENDING_OR_COMPLETE;
                }
                setModeBasedOnStatus();
            }

            public void validationError(List<String> fields) {
                status = Status.FULL_DB;
                setModeBasedOnStatus();
            }
        };
        AdminAuthResponder.getWikka(callback, "?wakka=AjaxFrSignup/ajax_frstatus");

    }

    private void setModeBasedOnStatus() {

        switch (status) {
        case UNREGISTERED:
            setupRegisterInfo();

            tabPanel.add(registerTable, "Ny bruker");
            tabPanel.selectTab(0);
            break;
        case PENDING_OR_COMPLETE:
            tabPanel.remove(registerTable);
            tabPanel.add(addStatusTable(), "Status");
            tabPanel.selectTab(0);
            break;
        case FULL_DB:
            tabPanel.add(new HTML("Fritt Regnskap tar ikke i mot flere nyregistreringer for \u00F8yeblikket"),
                    "Beklager stengt");
            tabPanel.selectTab(0);
            break;
        }
    }

    private Widget addStatusTable() {
        FlexTable ft = new FlexTable();
        ft.setText(0, 0, "Webadresse");
        ft.setText(0, 1, "Beskrivelse");
        ft.setText(0, 2, "Status");

        for (int i = 0; i < installations.size(); i++) {
            JSONObject install = installations.get(i).isObject();

            boolean complete = Util.getBoolean(install.get("status"));

            if (complete) {
                String name = Util.str(install.get("hostprefix")) + ".frittregnskap.no";
                String url = "http://" + name + "/prg/AccountingGWT.html";

                ft.setWidget(i + 1, 0, new Anchor(name, url));
            } else {
                ft.setText(i + 1, 0, Util.str(install.get("hostprefix")) + ".frittregnskap.no");
            }
            ft.setText(i + 1, 1, Util.str(install.get("description")));
            ft.setText(i + 1, 2, complete ? "Godkjent" : "Under godkjenning");
        }

        return ft;
    }

    private void setupRegisterInfo() {
        registerTable.setText(0, 0, "Registrering for regnskapsbruker");
        registerTable.getFlexCellFormatter().setColSpan(0, 0, 2);
        registerTable.getCellFormatter().setStyleName(0, 0, "gwtheader");

        nameGivesWidget = new HashMap<String, TextBoxWithErrorText>();

        addRow("Superbruker*", "superuser");
        addRow("Superbruker passord*", "password");
        addRow("Domenenavn*", "domainname");
        addRow("Klubbnavn*", "clubname");
        addRow("Kontaktperson*", "contact");
        addRow("E-post adresse*", "email");
        addRow("Adresse*", "address");
        addRow("Postnummer*", "zipcode");
        addRow("Sted*", "city");
        addRow("Telefon", "phone");

        registerButton = new NamedButton("register", "Opprett regnskapssystem");
        registerButton.addClickHandler(this);
        cancelButton = new NamedButton("cancel", "Avbryt");
        cancelButton.addClickHandler(this);

        FlowPanel hp = new FlowPanel();
        hp.add(registerButton);
        hp.add(cancelButton);

        int row = registerTable.getRowCount();
        registerTable.setWidget(row, 0, hp);
        registerTable.getFlexCellFormatter().setColSpan(row, 0, 2);
    }

    private TextBoxWithErrorText addRow(String title, String uiName) {
        int row = registerTable.getRowCount();
        registerTable.setText(row, 0, title);
        TextBoxWithErrorText t = new TextBoxWithErrorText(uiName);
        registerTable.setWidget(row, 1, t);

        nameGivesWidget.put(uiName, t);
        return t;
    }

    public void onClick(ClickEvent event) {
        if (event.getSource() == registerButton) {
            register();
        } else if (event.getSource() == cancelButton) {
            Util.forward("/");
        }
    }

    private void register() {
        if (!valider()) {
            return;
        }

        ServerResponse callback = new ServerResponse() {

            public void serverResponse(JSONValue responseObj) {
                JSONObject object = responseObj.isObject();
                String secret = Util.str(object.get("secret"));
                String wikilogin = Util.str(object.get("wikilogin"));

                callToCreateDatabase(secret, wikilogin);

            }
        };
        AdminAuthResponder.getWikka(callback, "?wakka=AjaxFrSignup/ajax_frsignup");

    }

    protected void callToCreateDatabase(String secret, String wikilogin) {

        StringBuffer parameters = new StringBuffer();

        Util.addPostParam(parameters, "secret", secret);
        Util.addPostParam(parameters, "wikilogin", wikilogin);

        Set<Entry<String, TextBoxWithErrorText>> entrySet = nameGivesWidget.entrySet();

        for (Entry<String, TextBoxWithErrorText> entry : entrySet) {
            Util.addPostParam(parameters, entry.getKey(), entry.getValue().getText());
        }

        ServerResponse callback = new ServerResponseWithValidation() {

            public void serverResponse(JSONValue responseObj) {
                init();
            }

            public void validationError(List<String> fields) {
                MasterValidator mv = new MasterValidator();
                for (String field : fields) {
                    mv.fail(nameGivesWidget.get(field), true, "Oppgitt verdi er ikke godkjent.");
                }
            }
        };
        AdminAuthResponder.post(callback, parameters, "admin/admin_install.php");

    }

    private boolean valider() {
        MasterValidator mv = new MasterValidator();
        for (int row = 1; row < registerTable.getRowCount() - 1; row++) {
            if (registerTable.getText(row, 0).contains("*")) {
                mv.mandatory("Feltet er p\u00E5krevd", registerTable.getWidget(row, 1));
            }

            if (registerTable.getText(row, 0).contains("Domenenavn")) {
                TextBoxWithErrorText box = (TextBoxWithErrorText) registerTable.getWidget(row, 1);
                if (!checkValidDomainName(box.getText())) {
                    mv.fail(box, true, "Bruk kun bokstaver a-z for domenenavn, korteste lengde er 3 tegn");
                }

            }
        }

        return mv.validateStatus();
    }

    private boolean checkValidDomainName(String text) {
        return text.matches("[a-z][a-z][a-z]+");
    }

    public static void setLoading() {
        // TODO Auto-generated method stub

    }

    public static void setDoneLoading() {
        // TODO Auto-generated method stub

    }
}
