//
//  FrittRegnskapViewController.m
//  FrittRegnskap
//
//  Created by Knut Erik Borgen on 08.08.10.
//  Copyright Knubo Borgen 2010. All rights reserved.
//

#import "FrittRegnskapViewController.h"

#import "MainView.h"
#import "GCPINViewController.h"

@implementation FrittRegnskapViewController


/*
// The designated initializer. Override to perform setup that is required before the view is loaded.
- (id)initWithNibName:(NSString *)nibNameOrNil bundle:(NSBundle *)nibBundleOrNil {
    if ((self = [super initWithNibName:nibNameOrNil bundle:nibBundleOrNil])) {
        // Custom initialization
    }
    return self;
}
*/

/*
// Implement loadView to create a view hierarchy programmatically, without using a nib.
- (void)loadView {
}
*/



// Implement viewDidLoad to do additional setup after loading the view, typically from a nib.
- (void)viewDidLoad {
	doReload = true;
       

}

/*
// Override to allow orientations other than the default portrait orientation.
- (BOOL)shouldAutorotateToInterfaceOrientation:(UIInterfaceOrientation)interfaceOrientation {
    // Return YES for supported orientations
    return (interfaceOrientation == UIInterfaceOrientationPortrait);
}
*/

- (void)didReceiveMemoryWarning {
	// Releases the view if it doesn't have a superview.
    [super didReceiveMemoryWarning];
	
	// Release any cached data, images, etc that aren't in use.
}

- (void)viewDidUnload {
	// Release any retained subviews of the main view.
	// e.g. self.myOutlet = nil;

}

- (void)dealloc {


    [super dealloc];
}

- (void) flagDataAsReloaded {
	doReload = true;
}

- (IBAction)showMemberships:(id)sender {
	
    hideOnLevel1 = false;
	[membershipViewUIController initView];
	
	[self setToolbarHidden:false animated:true];
	[UIView beginAnimations:nil context:NULL];
	[UIView setAnimationDuration:1.0];
	
	[UIView setAnimationTransition:UIViewAnimationTransitionCurlDown forView:self.view cache:NO];
	
	[self pushViewController:membershipViewUIController animated:false];
	
	[UIView commitAnimations];
}

- (IBAction)showPersons:(id)sender {

	hideOnLevel1 = true;
	[self setToolbarHidden:true animated:true];
	if(doReload) {
		doReload = false;
		[personViewController loadPeople];
	}
	
	[UIView beginAnimations:nil context:NULL];
	[UIView setAnimationDuration:1.0];

	[UIView setAnimationTransition:UIViewAnimationTransitionCurlDown forView:self.view cache:NO];
	
	[self pushViewController:personViewController animated:false];
	
	[UIView commitAnimations];
}		

- (IBAction)checkPIN {
    
    NSUserDefaults *userDefaults = [NSUserDefaults standardUserDefaults];  
    NSString *pincode = [userDefaults stringForKey:@"frittregnskap_pincode"];
    
    if([pincode length] == 0) {
        return;
    }
    
    GCPINViewController *PIN = [[GCPINViewController alloc]
                                initWithNibName:nil
                                bundle:nil
                                mode:GCPINViewControllerModeVerify];
    PIN.messageText = @"Skriv inn pinkode";
    PIN.errorText = @"Feil pinkode, 3 feil sletter alle data";
    PIN.title = @"Pinkode";
    PIN.verifyBlock = ^(NSString *code) {
        int feil = [userDefaults integerForKey:@"frittregnskap_feiltell"];

        NSLog(@"Sjekker kode: %@ %d", code, feil);
        
        BOOL sjekk = [code isEqualToString:pincode];
        
        if(!sjekk) {
            feil++;
        } else {
            feil = 0;
        }
        
        if(feil >= 3) {
            feil = 0;
            [appDelegate deleteAllObjectsInDatabase];
            sjekk = true;
            [userDefaults setObject:@"" forKey:@"frittregnskap_pincode"];
            [userDefaults setObject:@"" forKey:@"frittregnskap_username"];
            [userDefaults setObject:@"" forKey:@"frittregnskap_domain"];
            [userDefaults setObject:@"" forKey:@"frittregnskap_password"];

            NSLog(@"Slettet alle data");
        }
        
        [userDefaults setInteger:feil forKey:@"frittregnskap_feiltell"];
        
        return sjekk;
    };
    [PIN presentFromViewController:self animated:YES];
    [PIN release];
}


- (void) showPersonDetail:(Person*)person {
	[self setToolbarHidden:true animated:false];

	[self pushViewController:personDetailsController animated:true];
	[personDetailsController showPersonDetail:person];
}

- (void)navigationBar:(UINavigationBar *)navigationBar didPopItem:(UINavigationItem *)item {

	if([[navigationBar items] count] == 0) {
		[self setToolbarHidden:true animated:true];
	}

	if([[navigationBar items] count] == 1) {
		[self setToolbarHidden:hideOnLevel1 animated:true];
	}
	
}



@end
