package no.knubo.accounting.admin.client.misc;

import java.util.ArrayList;

import no.knubo.accounting.admin.client.SignupGWT;
import no.knubo.accounting.admin.client.ui.Util;

import com.google.gwt.http.client.Request;
import com.google.gwt.http.client.RequestBuilder;
import com.google.gwt.http.client.RequestCallback;
import com.google.gwt.http.client.RequestException;
import com.google.gwt.http.client.Response;
import com.google.gwt.json.client.JSONArray;
import com.google.gwt.json.client.JSONParser;
import com.google.gwt.json.client.JSONValue;
import com.google.gwt.user.client.Window;

public class AdminAuthResponder implements RequestCallback {

    private final ServerResponse callback;
    private static boolean noDB;

    private AdminAuthResponder(ServerResponse callback) {
        this.callback = callback;
        if (callback == null) {
            throw new RuntimeException("Callback cannot be null");
        }
        SignupGWT.setLoading();

    }

    public void onError(Request request, Throwable exception) {
        /* Not needed? */
    }

    public void onResponseReceived(Request request, Response response) {
        SignupGWT.setDoneLoading();
        if (response.getStatusCode() == 510) {
            Window.alert("Innlogging påkrevd - (noe som ikke er normalt...)");
        } else if (response.getStatusCode() == 511) {
            Window.alert("Ingen tilgang!");
        } else if (response.getStatusCode() == 512) {
            Window.alert("DB error:" + response.getText());
        } else if (response.getStatusCode() == 513) {
            JSONValue parse = JSONParser.parse(response.getText());

            ArrayList<String> fields = new ArrayList<String>();
            JSONArray array = parse.isArray();

            for (int i = 0; i < array.size(); i++) {
                fields.add(Util.str(array.get(i)));
            }

            if (callback instanceof ServerResponseWithValidation) {
                ((ServerResponseWithValidation) callback).validationError(fields);
            } else {
                Window.alert("Valideringsfeil:" + fields);
            }
        } else if (response.getStatusCode() == 514) {
            String data = response.getText();

            new MissingDataPopup(data).center();
        } else if (response.getStatusCode() == 515) {
            handleNODB();
        } else {
            String data = response.getText();
            if (data == null || data.length() == 0) {
                return;
            }
            data = data.trim();

            if (callback instanceof ServerResponseString) {
                ServerResponseString srs = (ServerResponseString) callback;
                srs.serverResponse(data);
                return;
            }

            JSONValue jsonValue = null;

            try {
                jsonValue = JSONParser.parse(data);
            } catch (Exception e) {
                Window.alert(e.getMessage());
                /* We catch this below in bad return data */
            }

            if (jsonValue == null) {
                if (callback instanceof ServerResponseWithErrorFeedback) {
                    ((ServerResponseWithErrorFeedback) callback).onError();
                } else {
                    // logger.error("baddata", data);
                    // Window.alert("Bad return data:" + data);
                }
            } else {
                try {
                    callback.serverResponse(jsonValue);
                } catch (Exception e) {
                    Util.log(e.toString());
                }
            }
        }
    }

    private void handleNODB() {
        if(!noDB) {
            noDB = true;
            Window.alert("Ingen forbindelse mot databasen.");
        }
    }

    public static void get(ServerResponse callback, String url) {
        RequestBuilder builder = new RequestBuilder(RequestBuilder.GET, baseURL() + url);

        try {
            builder.sendRequest("", new AdminAuthResponder(callback));
        } catch (RequestException e) {
            Window.alert("Failed to send the request: " + e.getMessage());
        }
    }

    public static void getWikka(ServerResponse callback, String url) {
        RequestBuilder builder = new RequestBuilder(RequestBuilder.GET, "/wakka/wikka.php" + url);
        
        try {
            builder.sendRequest("", new AdminAuthResponder(callback));
        } catch (RequestException e) {
            Window.alert("Failed to send the request: " + e.getMessage());
        }
    }

    public static void post(ServerResponse callback,
            StringBuffer parameters, String url) {

        RequestBuilder builder = new RequestBuilder(RequestBuilder.POST, baseURL() + url);

        try {
            builder.setHeader("Content-Type", "application/x-www-form-urlencoded");
            builder.sendRequest(parameters.toString(), new AdminAuthResponder(callback));
        } catch (RequestException e) {
            Window.alert("Failed to send the request: " + e.getMessage());
        }

    }

    private static String baseURL() {
        return "/RegnskapServer/services/";
    }
}
