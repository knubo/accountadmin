//
//  FrittRegnskapViewController.h
//  FrittRegnskap
//
//  Created by Knut Erik Borgen on 08.08.10.
//  Copyright __MyCompanyName__ 2010. All rights reserved.
//

#import <UIKit/UIKit.h>
#import "PersonUIViewController.h"
#import "PersonDetailsUIController.h"
#import "MembersViewUIController.h"
#import "FrittRegnskapAppDelegate.h"


@interface FrittRegnskapViewController : UINavigationController {
	IBOutlet PersonUIViewController *personViewController;
	IBOutlet PersonDetailsUIController *personDetailsController;
	IBOutlet MembersViewUIController *membershipViewUIController;
	IBOutlet FrittRegnskapAppDelegate *appDelegate;
    
	bool doReload;
	bool hideOnLevel1;
}

- (void) showPersonDetail:(Person*)person;
- (IBAction)showPersons:(id)sender;
- (void) flagDataAsReloaded;
- (IBAction)showMemberships:(id)sender;

- (IBAction)checkPIN;

@end

