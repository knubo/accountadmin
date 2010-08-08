//
//  MainView.h
//
//  Created by Knut Erik Borgen on 08.08.10.
//  Copyright 2010 __MyCompanyName__. All rights reserved.
//

#import <UIKit/UIKit.h>
#import <Foundation/Foundation.h>

@interface MainView : UIView {
    IBOutlet UIView *config;
}
- (IBAction)hideConfig:(id)sender;
- (IBAction)showConfig:(id)sender;
@end
