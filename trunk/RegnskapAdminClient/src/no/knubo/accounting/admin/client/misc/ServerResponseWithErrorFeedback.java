package no.knubo.accounting.admin.client.misc;

public interface ServerResponseWithErrorFeedback extends ServerResponse {

    public void onError();
}
