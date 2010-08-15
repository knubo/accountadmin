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

@interface FrittRegnskapViewController : UINavigationController {
	IBOutlet PersonUIViewController *personViewController;
	IBOutlet PersonDetailsUIController *personDetailsController;
}

- (void) showPersonDetail;
- (IBAction)showPersons:(id)sender;


@end

