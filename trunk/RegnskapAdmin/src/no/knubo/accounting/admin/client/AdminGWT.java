package no.knubo.accounting.admin.client;

import com.google.gwt.core.client.EntryPoint;
import com.google.gwt.user.client.ui.DockPanel;
import com.google.gwt.user.client.ui.HorizontalPanel;
import com.google.gwt.user.client.ui.RootPanel;

/**
 * Entry point classes define <code>onModuleLoad()</code>.
 */
public class AdminGWT implements EntryPoint {
    public void onModuleLoad() {
        DockPanel dp = new DockPanel();

        HorizontalPanel hp = new HorizontalPanel();
        
        dp.add(hp, DockPanel.NORTH);
        
        RootPanel.get().add(dp);
    }
}
